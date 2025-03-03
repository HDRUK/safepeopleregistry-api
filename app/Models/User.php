<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\SearchManager;

/**
 * App\Models\User
 *
 * @property mixed $unreadNotifications
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SearchManager;

    public const GROUP_USERS = 'USERS';
    public const GROUP_ORGANISATIONS = 'ORGANISATIONS';
    public const GROUP_ADMINS = 'ADMINS';
    public const GROUP_CUSTODIANS = 'CUSTODIANS';

    public const GROUP_KC_USERS = '\Researchers';
    public const GROUP_KC_ORGANISATIONS = '\Organisations';
    public const GROUP_KC_CUSTODIANS = '\Custodians';
    public const GROUP_KC_ADMINS = '\Admins';

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
        'public_opt_in',
        'declaration_signed',
        'organisation_id',
        'custodian_id',
        'custodian_user_id',
        'orc_id',
        'orcid_scanning',
        'orcid_scanning_completed_at',
        'is_delegate',
        'is_org_admin',
        'role',
        'location',
        't_and_c_agreed',
        't_and_c_agreement_date'
    ];

    protected static array $searchableColumns = [
        'first_name',
        'last_name',
        'email',
    ];

    protected static array $sortableColumns = [
        'first_name',
        'last_name',
        'email',
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
        'orcid_scanning' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'user_has_custodian_permissions',
        );
    }

    public function approvals(): BelongsToMany
    {
        return $this->belongsToMany(
            Custodian::class,
            'user_has_custodian_approvals',
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

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(
            Organisation::class
        );
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(
            Custodian::class
        );
    }

    public function custodian_user(): BelongsTo
    {
        return $this->belongsTo(
            CustodianUser::class
        );
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            Department::class,
            'user_has_departments'
        );
    }

    public static function searchByEmail(string $email): \stdClass|null
    {
        $results = DB::select(
            '
            SELECT \'users\' as source, registry_id
            FROM users
            where email = ?

            UNION ALL
            
            SELECT \'affiliations\' as source, registry_id
            FROM affiliations
            WHERE email = ?
            ',
            [$email, $email]
        );

        $records = collect($results)->toArray();
        if (count($records)) {
            return $records[0];
        }

        return null;
    }

    public function actionLogs()
    {
        return $this->morphMany(ActionLog::class, 'entity');
    }

}
