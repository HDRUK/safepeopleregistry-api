<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistryHasOrganisation extends Model
{
    use HasFactory;

    protected $table = 'registry_has_organisations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'organisation_id',
    ];

    /**
    * Get the organisation that belongs to this registry entry.
    */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }
}
