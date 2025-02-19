<?php

namespace App\Models;

use App\Observers\RegistryObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([RegistryObserver::class])]
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
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'registry_id');
    }

    public function identity(): HasOne
    {
        return $this->hasOne(Identity::class, 'registry_id');
    }

    public function affiliations(): BelongsToMany
    {
        return $this->belongsToMany(
            Affiliation::class,
            'registry_has_affiliations',
            'registry_id',
            'affiliation_id',
        );
    }

    public function education(): HasMany
    {
        return $this->hasMany(Education::class, 'registry_id');
    }

    public function training(): HasMany
    {
        return $this->hasMany(Training::class, 'registry_id');
    }

    public function professionalRegistrations(): HasMany
    {
        return $this->hasMany(Training::class, 'registry_id');
    }

    public function accreditations(): BelongsToMany
    {
        return $this->belongsToMany(
            Accreditation::class,            // Related model
            'registry_has_accreditations',   // Pivot table name
            'registry_id',                   // Foreign key on the pivot table for Registry
            'accreditation_id'               // Foreign key on the pivot table for Accreditation
        );
    }

    // Removed for now, in favour of a future _has_ relation as registry_id
    // didn't lend itself to Projects now. Especially when receiving pushes
    // from TRE/SDEs.
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
