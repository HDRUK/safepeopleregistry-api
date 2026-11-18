<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Exercises the real docker/start.sh (the container entrypoint) with a fake
 * `php` binary on PATH, so we can simulate Octane / the queue worker
 * crashing or receiving a stop signal without a real app.
 *
 * The contract under test: if either long-running service dies, the
 * container must exit so Kubernetes restarts the pod — it must never sit
 * there serving HTTP with no queue worker behind it.
 *
 * See docs/ARCHITECTURE/QUEUE_WORKERS.md.
 */
class DockerStartScriptTest extends TestCase
{
    private const SCRIPT_TIMEOUT_SECONDS = 15;

    // Literal rather than the pcntl SIGTERM constant, so pcntl is not required to run this test.
    private const SIGTERM = 15;

    private string $tmp;

    private string $log;

    protected function setUp(): void
    {
        parent::setUp();

        // `wait -n` (bash >= 4.3) is required by the entrypoint. The container
        // and CI runner ship bash 5; stock macOS ships 3.2.
        $version = trim((new Process(['bash', '-c', 'echo "${BASH_VERSINFO[0]}.${BASH_VERSINFO[1]}"']))->mustRun()->getOutput());
        if (version_compare($version, '4.3', '<')) {
            $this->markTestSkipped("docker/start.sh needs bash >= 4.3, found {$version}. Runs in CI and the container.");
        }
        if ((new Process(['sh', '-c', 'command -v setsid']))->run() !== 0) {
            $this->markTestSkipped('setsid (util-linux) is not available.');
        }

        $this->tmp = sys_get_temp_dir().'/start-sh-test-'.bin2hex(random_bytes(6));
        mkdir($this->tmp.'/bin', 0777, true);
        $this->log = $this->tmp.'/calls.log';
        touch($this->log);

        file_put_contents($this->tmp.'/bin/php', <<<'BASH'
            #!/usr/bin/env bash
            # Fake php: records every invocation; behaves as a long-running
            # service for octane / queue processes, and succeeds instantly for
            # everything else (migrate, seeders, deploy:run ...).
            echo "$*" >> "$FAKE_LOG"

            case "$*" in
              *octane:*)                 role=octane; mode="$FAKE_OCTANE" ;;
              *horizon*|*queue:work*)    role=queue;  mode="$FAKE_QUEUE"  ;;
              *)                         exit 0 ;;
            esac

            echo "$role:started" >> "$FAKE_LOG"
            trap 'echo "$role:TERM" >> "$FAKE_LOG"; kill "$child" 2>/dev/null; exit 0' TERM

            if [ "$mode" = "crash" ]; then
              sleep 0.5
              echo "$role:crashed" >> "$FAKE_LOG"
              exit 1
            fi

            sleep 60 & child=$!
            wait "$child"
            BASH);
        chmod($this->tmp.'/bin/php', 0755);
    }

    protected function tearDown(): void
    {
        if (isset($this->tmp) && is_dir($this->tmp)) {
            (new Process(['rm', '-rf', $this->tmp]))->run();
        }

        parent::tearDown();
    }

    public function test_container_exits_when_the_queue_worker_crashes(): void
    {
        $process = $this->startScript(octane: 'run', queue: 'crash');

        $this->waitOrFail($process, 'start.sh kept running (serving HTTP) after the queue worker crashed, so the pod would never be restarted.');

        $this->assertNotSame(0, $process->getExitCode(), 'A crashed queue worker must produce a non-zero exit so the pod is restarted.');
        $this->assertLogContains('queue:crashed');
        $this->assertLogContains('octane:TERM', 'Octane should be stopped cleanly when the queue worker dies.');
    }

    public function test_container_exits_when_octane_crashes(): void
    {
        $process = $this->startScript(octane: 'crash', queue: 'run');

        $this->waitOrFail($process, 'start.sh kept running after Octane crashed.');

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertLogContains('queue:TERM', 'The queue worker should be stopped cleanly when Octane dies.');
    }

    public function test_queues_are_processed_by_horizon(): void
    {
        $process = $this->startScript(octane: 'run', queue: 'run');
        $this->waitForLog(['octane:started', 'queue:started']);
        $process->signal(self::SIGTERM);
        $this->waitOrFail($process, 'start.sh did not stop on SIGTERM.');

        $this->assertLogContains('artisan horizon');
        $this->assertStringNotContainsString('queue:work', $this->logContents());
    }

    public function test_sigterm_is_forwarded_to_octane_and_horizon_for_a_graceful_shutdown(): void
    {
        $process = $this->startScript(octane: 'run', queue: 'run');
        $this->waitForLog(['octane:started', 'queue:started']);

        $process->signal(self::SIGTERM);
        $this->waitOrFail($process, 'start.sh did not stop on SIGTERM.');

        $this->assertLogContains('octane:TERM');
        $this->assertLogContains('queue:TERM', 'Horizon needs a real SIGTERM to finish in-flight jobs and release its lock.');
        $this->assertSame(0, $process->getExitCode(), 'A requested shutdown is not a failure.');
    }

    private function startScript(string $octane, string $queue): Process
    {
        // setsid: run in its own process group, so a `kill 0` in the script
        // can never take the test runner down with it.
        $process = new Process(
            ['setsid', 'bash', dirname(__DIR__, 2).'/docker/start.sh'],
            $this->tmp,
            [
                'PATH' => $this->tmp.'/bin:'.getenv('PATH'),
                'APP_ENV' => 'dev',
                'FAKE_LOG' => $this->log,
                'FAKE_OCTANE' => $octane,
                'FAKE_QUEUE' => $queue,
            ],
        );
        $process->setTimeout(self::SCRIPT_TIMEOUT_SECONDS);
        $process->start();

        return $process;
    }

    private function waitOrFail(Process $process, string $message): void
    {
        try {
            $process->wait();
        } catch (ProcessTimedOutException) {
            $this->fail($message."\nCalls:\n".$this->logContents());
        }
    }

    /** @param string[] $needles */
    private function waitForLog(array $needles): void
    {
        $deadline = microtime(true) + 10;
        while (microtime(true) < $deadline) {
            $log = $this->logContents();
            if (array_filter($needles, fn ($n) => ! str_contains($log, $n)) === []) {
                return;
            }
            usleep(100_000);
        }

        $this->fail("Services never started. Calls:\n".$this->logContents());
    }

    private function assertLogContains(string $needle, string $message = ''): void
    {
        $this->assertStringContainsString($needle, $this->logContents(), $message);
    }

    private function logContents(): string
    {
        return (string) file_get_contents($this->log);
    }
}
