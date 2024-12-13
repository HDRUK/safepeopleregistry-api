<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class History extends Model
{
    use HasFactory;

    protected $table = 'histories';

    public $timestamps = true;

    protected $fillable = [
        'employment_id',
        'endorsement_id',
        'infringement_id',
        'project_id',
        'access_key_id',
        'custodian_identifier',
        'ledger_hash',
    ];

    public function endorsements(): BelongsToMany
    {
        return $this->belongsToMany(
            Endorsement::class,
            'history_has_endorsements',
        );
    }

    public function infringements(): BelongsToMany
    {
        return $this->belongsToMany(
            Infringement::class,
            'history_has_infringements',
        );
    }
}
