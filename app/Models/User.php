<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'registry_id',
        'provider',
        'keycloak_id',
        'user_group',
        'consent_scrape',
        'profile_steps_completed',
        'profile_completed_at',
        'unclaimed',
        'feed_source',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'provider',
        'password',
        'keycloak_id',
        'otp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'consent_scrape' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'user_has_issuer_permissions',
        );
    }

    public function approvals(): BelongsToMany
    {
        return $this->belongsToMany(
            Issuer::class,
            'user_has_issuer_approvals',
        );
    }

    public function registry(): BelongsTo
    {
        return $this->belongsTo(
            Registry::class
        );
    }

    public function pendingInvites(): HasMany
    {
        return $this->hasMany(PendingInvite::class);
    }
}
