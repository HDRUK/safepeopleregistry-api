<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'registries';

    public $timestamps = true;

    protected $fillable = [
        'dl_ident',
        'pp_ident',
        'digi_ident',
        'verified',
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

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

    public function employment(): HasOne
    {
        return $this->hasOne(Employment::class, 'registry_id');
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

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'registry_has_files');
    }
}
