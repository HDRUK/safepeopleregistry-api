# Queue workers & process supervision

How the API container runs its HTTP server and queue workers, and what happens
when one of them dies.

## Overview

The container entrypoint is `docker/start.sh` (PID 1). After migrations,
seeders and `deploy:run`, it starts two long-running services:

| Service | Command | Responsible for |
| ------- | ------- | --------------- |
| Octane  | `php artisan octane:frankenphp --port=8100 ...` | HTTP requests |
| Horizon | `php artisan horizon` | Queue workers on the `redis` connection |

This mirrors `gateway-api-2` (dev branch). Neither project uses supervisord.

## Supervision model (two layers)

1. **Horizon supervises the workers.** Horizon's master process starts, scales
   and restarts the individual worker processes (on crash, memory limit,
   timeout, etc.) according to `config/horizon.php`.
2. **Kubernetes supervises the container.** `start.sh` waits on both Octane and
   Horizon with `wait -n`. If *either* exits, for any reason and with any
   status, the script stops the other and exits non-zero. The pod is then
   restarted by Kubernetes.

The rule: **the container must never keep serving HTTP with no queue worker
behind it.** That was the previous failure mode (see below).

### Graceful shutdown

On `SIGTERM`/`SIGINT` (redeploy, rolling restart, scale-down) `start.sh`
forwards `SIGTERM` to both services and waits for them, then exits `0`.
Horizon needs a real `SIGTERM` to finish in-flight jobs and release its master
lock; without forwarding, it would be hard-killed when the container is torn
down.

## Horizon configuration

`config/horizon.php` defines one supervisor (`supervisor-1`) on the `default`
queue: `tries=3`, `backoff=5`, `timeout=120`, auto-balancing (the same
retry settings the old `queue:work` command used). Process counts vary per
`APP_ENV`: `prod`, `preprod`, `dev`, `local`.

Horizon matches `APP_ENV` against the `environments` keys and **starts no
workers at all if nothing matches**. A `'*'` entry is the final fallback
(one worker) so a misnamed environment degrades rather than silently
processing nothing. Keep `'*'` last: the first matching key wins.

If you dispatch to a new queue name, add it to `supervisor-1.queue`
(or a new supervisor), or nothing will consume it.

### Dashboard

`/horizon`, behind `auth:api` and the `viewHorizon` gate in
`app/Providers/HorizonServiceProvider.php` (admins only in `prod`, any
authenticated user elsewhere).

> **Data protection note:** Horizon stores job payloads in Redis (recent jobs
> for 60 minutes, failed jobs for 7 days, per `trim` in `config/horizon.php`)
> and shows them in the dashboard. Payloads for notification/email jobs may
> contain personal data. Consider whether the non-prod gate and retention
> windows are appropriate for your GDPR obligations.

## Background: why this changed

Previously `start.sh` ran `queue:work` in a backgrounded `while true` restart
loop and `exec`'d Octane as the main process. The script used `set -e`, which
the background subshell inherited, so when `queue:work` exited non-zero (fatal
error, memory exhaustion, Redis drop) the whole loop died instead of
restarting. Octane kept running, the pod looked healthy, and jobs piled up
unprocessed.

## Tests

`tests/Unit/DockerStartScriptTest.php` runs the real `docker/start.sh` with a
fake `php` on `PATH` and checks:

- queue worker crash → container exits non-zero, Octane is stopped;
- Octane crash → container exits non-zero, Horizon is stopped;
- queues are run by Horizon (not bare `queue:work`);
- `SIGTERM` is forwarded to both services and the container exits `0`.

It needs bash ≥ 4.3 and `setsid`, so it **skips on stock macOS** (bash 3.2).
It runs in CI (ubuntu). To run it locally:

```bash
docker run --rm -v "$PWD":/app -w /app php:8.4-cli \
  php vendor/bin/pest tests/Unit/DockerStartScriptTest.php
```
