<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @OA\Schema (
 *      schema="Registry",
 *      title="Registry",
 *      description="Registry model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example="1"
 *      ),
 *      @OA\Property(property="created_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(property="updated_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(property="deleted_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(property="digi_ident",
 *          type="string",
 *          example="$2y$12$Ssrz04d0bfw2X9t3juq9K.WPUgPNplXr1FHbdjoTeLajgVGGRxiqG"
 *      ),
 *      @OA\Property(property="dl_ident",
 *          type="string",
 *          example=""
 *      ),
 *      @OA\Property(property="pp_ident",
 *          type="string",
 *          example=""
 *      ),
 *      @OA\Property(property="verified",
 *          type="integer",
 *          example="1"
 *      ),
 * )
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $dl_ident
 * @property string|null $pp_ident
 * @property string $digi_ident
 * @property bool $verified
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accreditation> $accreditations
 * @property-read int|null $accreditations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Affiliation> $affiliations
 * @property-read int|null $affiliations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Education> $education
 * @property-read int|null $education_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\History> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\Identity|null $identity
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Training> $professionalRegistrations
 * @property-read int|null $professional_registrations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectHasUser> $projectUsers
 * @property-read int|null $project_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Training> $trainings
 * @property-read int|null $trainings_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\RegistryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereDigiIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereDlIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry wherePpIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry withoutTrashed()
 * @mixin \Eloquent
 */
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

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\User>
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'registry_id');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\ProjectHasUser>
     */
    public function projectUsers(): HasMany
    {
        return $this->hasMany(ProjectHasUser::class, 'user_digital_ident', 'digi_ident');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Identity>
     */
    public function identity(): HasOne
    {
        return $this->hasOne(Identity::class, 'registry_id');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Affiliation>
     */
    public function affiliations(): BelongsToMany
    {
        return $this->belongsToMany(
            Affiliation::class,
            'affiliation',
            'registry_id',
            'affiliation_id',
        );
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Education>
     */
    public function education(): HasMany
    {
        return $this->hasMany(Education::class, 'registry_id');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Training>
     */
    public function professionalRegistrations(): HasMany
    {
        return $this->hasMany(Training::class, 'registry_id');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Accreditation>
     */
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

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Training>
     */
    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'registry_has_trainings');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\History>
     */
    public function history(): BelongsToMany
    {
        return $this->belongsToMany(History::class, 'registry_has_histories');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\File>
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'registry_has_files');
    }
}
