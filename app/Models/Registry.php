<?php

namespace App\Models;

use App\Models\History;
use App\Models\Training;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'digi_ident',
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
        'user_id',
        'dl_ident',
        'pp_ident',
        'digi_ident',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'registry_id');
    }

    public function identity(): HasOne
    {
        return $this->hasOne(Identity::class, 'registry_id');
    }
  
    public function training(): HasMany
    {
        return $this->hasMany(Training::class, 'registry_id');
    }

    // Removed for now, in favour of a future _has_ relation as registry_id
    // didn't lend itself to Projects now. Especially when receiving pushes
    // from TRE/SDE issuers.
    //
    // public function projects(): HasMany
    // {
    //     return $this->hasMany(Project::class, 'registry_id');
    // }

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'registry_has_organisations');
    }

    public function history(): BelongsToMany
    {
        return $this->belongsToMany(History::class, 'registry_has_histories');
    }
}
