<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistryHasAffiliation extends Model
{
    use HasFactory;

    public $table = 'registry_has_affiliations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'affiliation_id',
    ];

    public function affiliation(): BelongsTo
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id');
    }
}
