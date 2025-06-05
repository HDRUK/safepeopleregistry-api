<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationHasSubsidiary extends Model
{
    use HasFactory;

    public $table = 'organisation_has_subsidiaries';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'subsidiary_id',
    ];

    /**
     * Get the organisation associated with this record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Organisation>
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    /**
     * Get the subsidiary associated with this record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Subsidiary>
     */
    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class, 'subsidiary_id');
    }
}
