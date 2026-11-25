<?php

namespace App\Http\Resources\CustodianExport;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ExportProjectResource extends JsonResource
{
    use ExportsStatus;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unique_id' => $this->unique_id,
            'title' => $this->title,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => self::status($this->modelState),
        ];
    }
}
