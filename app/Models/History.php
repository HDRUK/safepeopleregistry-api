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
        'affiliation_id',
        'endorsement_id',
        'infringement_id',
        'project_id',
        'access_key_id',
        'custodian_identifier',
        'ledger_hash',
    ];

    /**
     * Get the endorsements associated with this history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Endorsement>
     */
    public function endorsements(): BelongsToMany
    {
        return $this->belongsToMany(
            Endorsement::class,
            'history_has_endorsements',
        );
    }

    /**
     * Get the infringements associated with this history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Infringement>
     */
    public function infringements(): BelongsToMany
    {
        return $this->belongsToMany(
            Infringement::class,
            'history_has_infringements',
        );
    }
}
