<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class CleanCustodianModelConfigTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-custodian-model-config-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::statement("CREATE TEMPORARY TABLE temp_custodian_model_configs LIKE custodian_model_configs");

        DB::statement("
            INSERT INTO temp_custodian_model_configs (id, created_at, updated_at, entity_model_id, active, custodian_id)
            SELECT c1.*
            FROM custodian_model_configs c1
            JOIN (
                SELECT entity_model_id, custodian_id, MAX(updated_at) AS max_updated
                FROM custodian_model_configs
                GROUP BY entity_model_id, custodian_id
            ) c2
            ON c1.entity_model_id = c2.entity_model_id
            AND c1.custodian_id   = c2.custodian_id
            AND (c1.updated_at = c2.max_updated OR (c1.updated_at IS NULL AND c2.max_updated IS NULL))
        ");

        DB::statement("TRUNCATE custodian_model_configs");

        DB::statement("INSERT INTO custodian_model_configs SELECT * FROM temp_custodian_model_configs");

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_custodian_model_configs");
    }
}
