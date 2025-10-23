<?php

use App\Models\ProjectDetail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $projectDetails = ProjectDetail::all();

        Schema::table('project_details', function (Blueprint $table) {
            $table->json('research_outputs')->change();
        });

        foreach ($projectDetails as $projectDetail) {
            $researchOutputs = json_decode($projectDetail->research_outputs, true);

            if (is_array($researchOutputs) && array_key_exists('research_outputs', $researchOutputs)) {
                ProjectDetail::where('id', $projectDetail->id)
                    ->update(['research_outputs' => json_encode($researchOutputs['research_outputs'])]);
            }

            if (is_array($researchOutputs) && !array_key_exists('research_outputs', $researchOutputs)) {
                ProjectDetail::where('id', $projectDetail->id)
                    ->update(['research_outputs' => json_encode($researchOutputs)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $projectDetails = ProjectDetail::all();

        Schema::table('project_details', function (Blueprint $table) {
            $table->mediumText('research_outputs')->change();
        });

        foreach ($projectDetails as $projectDetail) {
            $researchOutputs = json_decode($projectDetail->research_outputs, true);

            ProjectDetail::where('id', $projectDetail->id)
                ->update(['research_outputs' => json_encode(['research_outputs' => $researchOutputs])]);
        }
    }
};
