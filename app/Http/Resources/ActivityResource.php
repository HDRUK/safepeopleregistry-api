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
            'causer'       => $this->whenLoaded('causer', fn () => $this->formatCauserSubject($this->causer)),
            'subject'      => $this->whenLoaded('subject', fn () => $this->formatCauserSubject($this->subject)),
            'created_at'   => optional($this->created_at)->toIso8601String(),
        ];
    }

    /**
     * Subject formatting:
     * - Affiliation => collapse to registry.user (id, first_name, last_name)
     * - User / Organisation / ValidationLog => trimmed fields
     */
    protected function formatCauserSubject($model): array|null
    {
        if (is_null($model)) {
            return null;
        }

        if ($model instanceof Affiliation) {
            $u = $model->registry?->user;
            return $u ? [
                'id'         => $u->id,
                'first_name' => $u->first_name,
                'last_name'  => $u->last_name,
            ] : null;
        }

        if ($model instanceof User) {
            return [
                'id'         => $model->id,
                'first_name' => $model->first_name,
                'last_name'  => $model->last_name,
            ];
        }

        if ($model instanceof Organisation) {
            return [
                'id'                => $model->id,
                'organisation_name' => $model->organisation_name,
            ];
        }

        if ($model instanceof ValidationLog) {
            return [
                'id' => $model->id,
            ];
        }

        return null;
    }
}
