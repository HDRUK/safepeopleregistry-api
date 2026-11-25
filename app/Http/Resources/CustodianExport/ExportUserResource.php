<?php

namespace App\Http\Resources\CustodianExport;

use App\Models\Accreditation;
use App\Models\Affiliation;
use App\Models\CustodianHasProjectUser;
use App\Models\ProjectHasUser;
use App\Models\Registry;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Registry
 */
class ExportUserResource extends JsonResource
{
    use ExportsStatus;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'digi_ident' => $this->digi_ident,
            'first_name' => $this->user?->first_name,
            'last_name' => $this->user?->last_name,
            'orcid' => $this->user?->orc_id,
            'identity_verified' => (bool) $this->identity?->idvt_success,
            'affiliations' => $this->affiliations
                ->map(fn (Affiliation $affiliation) => self::exportAffiliation($affiliation))
                ->values()
                ->all(),
            'trainings' => $this->trainings->map(fn (Training $training) => [
                'training_name' => $training->training_name,
                'provider' => $training->provider,
                'awarded_at' => $training->awarded_at,
                'expires_at' => $training->expires_at,
                'pro_registration' => (bool) $training->pro_registration,
            ])->values()->all(),
            'accreditations' => $this->accreditations->map(fn (Accreditation $accreditation) => [
                'associated_organisation_name' => $accreditation->associated_organisation_name,
                'issue_date' => $accreditation->issue_date,
                'expiry_date' => $accreditation->expiry_date,
            ])->values()->all(),
            'projects' => $this->exportProjects(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exportProjects(): array
    {
        $projects = [];

        /** @var ProjectHasUser $projectUser */
        foreach ($this->projectUsers as $projectUser) {
            /** @var CustodianHasProjectUser $approval */
            foreach ($projectUser->custodianHasProjectUser as $approval) {
                $projects[] = [
                    'project_id' => $projectUser->project?->id,
                    'project_unique_id' => $projectUser->project?->unique_id,
                    'project_title' => $projectUser->project?->title,
                    'project_role' => $projectUser->role?->name,
                    'validation_status' => self::status($approval->modelState),
                    'affiliation' => $projectUser->affiliation
                        ? self::exportAffiliation($projectUser->affiliation)
                        : null,
                ];
            }
        }

        return $projects;
    }

    /**
     * @return array<string, mixed>
     */
    private static function exportAffiliation(Affiliation $affiliation): array
    {
        return [
            'id' => $affiliation->id,
            'organisation_name' => $affiliation->organisation?->organisation_name,
            'relationship' => $affiliation->relationship,
            'role' => $affiliation->role,
            'department' => $affiliation->department,
            'from' => $affiliation->from,
            'to' => $affiliation->to,
            'current_employer' => (bool) $affiliation->current_employer,
            'status' => self::status($affiliation->modelState),
        ];
    }
}
