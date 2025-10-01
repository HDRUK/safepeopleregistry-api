<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\Affiliation;
use App\Models\Organisation;
use Illuminate\Http\Request;
use App\Models\ValidationLog;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Tell PHPStan that this resource “acts like” Activity,
 * and that $resource is an Activity instance.
 *
 * @mixin \Spatie\Activitylog\Models\Activity
 * @property-read \Spatie\Activitylog\Models\Activity $resource
 */
class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'log_name'     => $this->log_name,
            'description'  => $this->description,
            'subject_type' => $this->subject_type,
            'event'        => $this->event,
            'subject_id'   => $this->subject_id,
            'causer_type'  => $this->causer_type,
            'causer_id'    => $this->causer_id,
            'properties'   => $this->properties?->toArray() ?? [],
            'batch_uuid'   => $this->batch_uuid,
            'causer'       => $this->whenLoaded('causer', fn () => $this->formatCauser()),
            'subject'      => $this->whenLoaded('subject', fn () => $this->formatSubject()),
            'created_at'   => optional($this->created_at)->toIso8601String(),
        ];
    }

    /**
     * Subject formatting:
     * - Affiliation => collapse to registry.user (id, first_name, last_name)
     * - User / Organisation / ValidationLog => trimmed fields
     */
    protected function formatSubject()
    {
        $subject = $this->subject;

        if ($subject instanceof Affiliation) {
            $u = $subject->registry?->user;
            return $u ? [
                'id'         => $u->id,
                'first_name' => $u->first_name,
                'last_name'  => $u->last_name,
            ] : null;
        }

        if ($subject instanceof User) {
            return [
                'id'         => $subject->id,
                'first_name' => $subject->first_name,
                'last_name'  => $subject->last_name,
            ];
        }

        if ($subject instanceof Organisation) {
            return [
                'id'                => $subject->id,
                'organisation_name' => $subject->organisation_name,
            ];
        }

        if ($subject instanceof ValidationLog) {
            return [
                'id' => $subject->id,
            ];
        }

        // Unknown or null subject
        return null;
    }

    /**
     * Causer formatting (mirrors subject rules but allows null).
     * - null => null
     * - Affiliation => collapse to registry.user (id, first_name, last_name)
     * - User / Organisation / ValidationLog => trimmed fields
     */
    protected function formatCauser()
    {
        $causer = $this->causer;

        if (is_null($causer)) {
            return null;
        }

        if ($causer instanceof Affiliation) {
            $u = $causer->registry?->user;
            return $u ? [
                'id'         => $u->id,
                'first_name' => $u->first_name,
                'last_name'  => $u->last_name,
            ] : null;
        }

        if ($causer instanceof User) {
            return [
                'id'         => $causer->id,
                'first_name' => $causer->first_name,
                'last_name'  => $causer->last_name,
            ];
        }

        if ($causer instanceof Organisation) {
            return [
                'id'                => $causer->id,
                'organisation_name' => $causer->organisation_name,
            ];
        }

        if ($causer instanceof ValidationLog) {
            return [
                'id' => $causer->id,
            ];
        }

        // Unknown causer type (shouldn’t happen if you filtered with ALLOWED_TYPES)
        return null;
    }
}
