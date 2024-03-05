<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Registry extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'registries';

    /**
     * Whether or not this model supports timestamps
     * 
     * @var bool
     */
    public $timestamps = true;

    /**
     * What fields of this model are accepted as parameters
     * 
     * @var array
     */
    protected $fillable = [
        'user_id',
        'dl_ident',
        'pp_ident',
        'verified',
    ];

    /**
     * Whether or not we have to ask Laravel to cast fields
     * 
     * @var array
     */
    protected $casts = [
        'verified' => 'boolean',
    ];

    /**
     * Whether or not we want certain fields hidden from the
     * payload
     * 
     * @var array
     */
    protected $hidden = [
        'dl_ident',
        'pp_ident',
    ];

    // Stub relations for later work
    //
    // public function training(): BelongsToMany
    // {
    //     return $this->belongsToMany(Training::class, 'registry_has_trainings');
    // }

    // public function projects(): BelongsToMany
    // {
    //     return $this->belongsToMany(Project::class, 'registry_has_projects');
    // }

    // public function affiliation(): BelongsToMany
    // {
    //     return $this->belongsToMany(Affiliation::class, 'registry_has_affiliations');
    // }

    // public function history(): BelongsToMany
    // {
    //     return $this->belongsToMany(History::class, 'registry_has_history');
    // }
}
