<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StripInternalEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:strip-internal-endpoints {path=storage/api-docs/api-docs.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes operations marked x-internal from a generated OpenAPI spec, run after l5-swagger:generate.';

    public function handle(): int
    {
        $path = base_path($this->argument('path'));

        if (!file_exists($path)) {
            $this->error("Spec not found at {$path}");

            return self::FAILURE;
        }

        $spec = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $removed = 0;

        foreach ($spec['paths'] ?? [] as $route => $operations) {
            foreach ($operations as $method => $operation) {
                if (!is_array($operation)) {
                    continue;
                }

                $internal = filter_var($operation['x-internal'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if (!$internal) {
                    continue;
                }

                unset($spec['paths'][$route][$method]);
                $removed++;
            }

            if (empty($spec['paths'][$route])) {
                unset($spec['paths'][$route]);
            }
        }

        file_put_contents($path, json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Removed {$removed} internal operation(s) from {$path}");

        return self::SUCCESS;
    }
}
