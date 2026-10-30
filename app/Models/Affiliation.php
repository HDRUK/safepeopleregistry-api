<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enums\AffiliationEndDateSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Traits\StateWorkflow;
use App\Traits\FilterManager;

/**
 * @OA\Schema (
 *      schema="Affiliation",
 *      title="Affiliation",
 *      description="Affiliation model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example=1,
 *          description="Model primary key"
 *      ),
 *      @OA\Property(property="created_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="updated_at",
 *          type="string",
 *          example="2023-10-10T15:43:00Z"
 *      ),
 *      @OA\Property(property="organisation_id",
 *          type="integer",
 *          example=1,
 *          description="Organisational link"
 *      ),
 *      @OA\Property(property="member_id",
 *          type="string",
 *          example="325987-skdjfh283429-lkfsfdh",
 *          description="Member ID UUID"
 *      ),
 *      @OA\Property(property="relationship",
 *          type="string",
 *          example="employee",
 *          description="Textual representation of affiliation relationship"
 *      ),
 *      @OA\Property(property="from",
 *          type="string",
 *          format="date",
 *          example="2023-01-12",
 *          description="Date affiliation commenced"
 *      ),
 *      @OA\Property(property="to",
 *          type="string",
 *          format="date",
 *          nullable=true,
 *          example="2024-12-01",
 *          description="Date affiliation ends (or ended). Null when there is no known end date"
 *      ),
 *      @OA\Property(property="end_date_recorded_at",
 *          type="string",
 *          format="date-time",
 *          nullable=true,
 *          description="When the end date was recorded"
 *      ),
 *      @OA\Property(property="end_date_recorded_by",
 *          type="integer",
 *          nullable=true,
 *          description="The user who recorded the end date"
 *      ),
 *      @OA\Property(property="end_date_source",
 *          type="string",
 *          enum={"user", "organisation"},
 *          nullable=true,
 *          description="Who recorded the end date. The organisation's date wins over the user's"
 *      ),
 *      @OA\Property(property="left_at",
 *          type="string",
 *          format="date-time",
 *          nullable=true,
 *          description="When the leaver flow ran for this affiliation"
 *      ),
 *      @OA\Property(property="department",
 *          type="string",
 *          example="Research & Development",
 *          description="Department worked during affiliation"
 *      ),
 *      @OA\Property(property="role",
 *          type="string",
 *          example="Principal Investigator (PI)",
 *          description="Role held during affiliation"
 *      ),
 *      @OA\Property(property="email",
 *          type="string",
 *          example="user@domain.com",
 *          description="Professional email held during affiliation"
 *      ),
 *      @OA\Property(property="ror",
 *          type="string",
 *          example="0hgyr56",
 *          description="The ROR.org identifier for this affiliation institute"
 *      ),
 *      @OA\Property(property="registry_id",
 *          type="integer",
 *          example=123,
 *          description="The Registry primary key associated with this affiliation"
 *      ),
 *      @OA\Property(property="current_employer",
 *          type="boolean",
 *          example=true,
 *          description="Flag indicating if affiliation is for the current employer"
 *      ),
 *      @OA\Property(property="verification_code",
 *          type="string",
 *          example="a9f34b1c-6d8f-47f2-9131-1d2b56a3e7df",
 *          description="Unique verification code issued for confirmation"
 *      ),
 *      @OA\Property(property="verification_sent_at",
 *          type="string",
 *          format="date-time",
 *          example="2025-09-18T12:45:00Z",
 *          description="Timestamp when verification code was sent"
 *      ),
 *      @OA\Property(property="verification_confirmed_at",
 *          type="string",
 *          format="date-time",
 *          example="2025-09-19T09:10:00Z",
 *          description="Timestamp when verification was confirmed"
 *      ),
 *      @OA\Property(property="is_verified",
 *          type="boolean",
 *          example=true,
 *          description="Flag indicating if affiliation is verified"
 *      )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $organisation_id
 * @property string $member_id
 * @property string|null $relationship
 * @property string|null $from
 * @property string|null $to
 * @property \Carbon\CarbonInterface|null $end_date_recorded_at
 * @property int|null $end_date_recorded_by
 * @property AffiliationEndDateSource|null $end_date_source
 * @property \Carbon\CarbonInterface|null $end_date_warning_sent_at
 * @property \Carbon\CarbonInterface|null $left_at
 * @property string|null $department
 * @property string|null $role
 * @property string|null $email
 * @property string|null $ror
 * @property int $registry_id
 * @property int|null $verdict_user_id
 * @property string|null $verdict_date_actioned
 * @property int|null $verdict_outcome
 * @property-read mixed $registry_affiliation_state
 * @property-read \App\Models\Organisation|null $organisation
 * @property-read \App\Models\Registry|null $registry
 * @method static \Database\Factories\AffiliationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereRor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereVerdictDateActioned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereVerdictOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereVerdictUserId($value)
 * @mixin \Eloquent
 */
class Affiliation extends Model
{
    use HasFactory;
    use StateWorkflow;
    use FilterManager;

    /**
     * States an affiliation can no longer leave. It is history: never edited, deleted or reused.
     */
    public const ENDED_STATES = [
        State::STATE_AFFILIATION_LEFT,
        State::STATE_AFFILIATION_NEVER_VERIFIED,
    ];

    /**
     * States in which the user has never verified their professional email, so an affiliation
     * that ends here becomes "never verified" rather than "left".
     */
    public const UNVERIFIED_STATES = [
        State::STATE_AFFILIATION_EMAIL_VERIFY,
        State::STATE_AFFILIATION_INVITED,
        State::STATE_AFFILIATION_INFO_REQUIRED,
    ];

    /**
     * Attributes owned by the leaver flow, which sends its own notifications when they change.
     */
    public const LEAVER_ATTRIBUTES = [
        'to',
        'current_employer',
        'end_date_recorded_at',
        'end_date_recorded_by',
        'end_date_source',
        'end_date_warning_sent_at',
        'left_at',
    ];

    // State::STATE_AFFILIATION_ORGANISATION_INVITED
    protected static array $transitions = [
        State::STATE_AFFILIATION_INVITED => [
            State::STATE_AFFILIATION_ACCOUNT_IN_PROGRESS,
            State::STATE_AFFILIATION_INFO_REQUIRED,
            State::STATE_AFFILIATION_ORGANISATION_INVITED,
            State::STATE_AFFILIATION_NEVER_VERIFIED,
        ],
        State::STATE_AFFILIATION_EMAIL_VERIFY => [
            State::STATE_AFFILIATION_PENDING,
            State::STATE_AFFILIATION_ORGANISATION_INVITED,
            State::STATE_AFFILIATION_NEVER_VERIFIED,
        ],
        State::STATE_AFFILIATION_ACCOUNT_IN_PROGRESS => [
            State::STATE_AFFILIATION_PENDING,
            State::STATE_AFFILIATION_REVIEW,
            State::STATE_AFFILIATION_LEFT,
        ],
        State::STATE_AFFILIATION_INFO_REQUIRED => [
            State::STATE_AFFILIATION_PENDING,
            State::STATE_AFFILIATION_NEVER_VERIFIED,
        ],
        State::STATE_AFFILIATION_ORGANISATION_INVITED => [
            State::STATE_AFFILIATION_PENDING,
            State::STATE_AFFILIATION_LEFT,
        ],
        State::STATE_AFFILIATION_REVIEW => [
            State::STATE_AFFILIATION_LEFT,
        ],
        State::STATE_AFFILIATION_PENDING => [
            State::STATE_AFFILIATION_EMAIL_VERIFY,
            State::STATE_AFFILIATION_APPROVED,
            State::STATE_AFFILIATION_REJECTED,
            State::STATE_AFFILIATION_LEFT,
        ],
        State::STATE_AFFILIATION_APPROVED => [
            State::STATE_AFFILIATION_EMAIL_VERIFY,
            State::STATE_AFFILIATION_REJECTED,
            State::STATE_AFFILIATION_LEFT,
        ],
        State::STATE_AFFILIATION_REJECTED => [
            State::STATE_AFFILIATION_APPROVED
        ],
        State::STATE_AFFILIATION_LEFT => [],
        State::STATE_AFFILIATION_NEVER_VERIFIED => [],
    ];

    public $table = 'affiliations';

    public $timestamps = true;

    protected $fillable = [
        'organisation_id',
        'member_id',
        'relationship',
        'from',
        'to',
        'department',
        'role',
        'email',
        'ror',
        'registry_id',
        'verdict_user_id',
        'verdict_date_actioned',
        'verdict_outcome',
        'current_employer',
        'orcid_organisation',
    ];

    protected $guarded = [
        'verification_code',
        'verification_sent_at',
        'verification_confirmed_at',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'current_employer' => 'boolean',
        'end_date_recorded_at' => 'datetime',
        'end_date_source' => AffiliationEndDateSource::class,
        'end_date_warning_sent_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    /**
     * Dates are stored as 'Y-m-d'. Blank input (the invite flows send '') means no date.
     */
    protected function from(): Attribute
    {
        return Attribute::make(set: fn ($value) => self::toDateString($value));
    }

    protected function to(): Attribute
    {
        return Attribute::make(set: fn ($value) => self::toDateString($value));
    }

    public static function toDateString(mixed $value): ?string
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }

    public static function getTransitions(): array
    {
        return static::$transitions;
    }

    /**
     * Get the organisation related to the affiliation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Organisation>
     */
    public function organisation()
    {
        return $this->belongsTo(
            Organisation::class,
            'organisation_id',
            'id'
        );
    }

    /**
     * Get the registry related to the affiliation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Registry>
     */
    public function registry()
    {
        return $this->belongsTo(
            Registry::class,
            'registry_id',
            'id'
        );
    }

    public function modelState(): MorphOne
    {
        return $this->morphOne(ModelState::class, 'stateable');
    }

    public function endDateRecordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'end_date_recorded_by');
    }

    public function projectUsers(): HasMany
    {
        return $this->hasMany(ProjectHasUser::class, 'affiliation_id');
    }

    /**
     * Affiliations whose end date has arrived but which have not yet been ended (or were rejected).
     */
    public function scopeDueToEnd(Builder $query): Builder
    {
        return $query
            ->whereNotNull('to')
            ->whereDate('to', '<=', Carbon::today())
            ->whereDoesntHave('modelState.state', function (Builder $query) {
                $query->whereIn('slug', [...self::ENDED_STATES, State::STATE_AFFILIATION_REJECTED]);
            });
    }

    /**
     * Affiliations that have not ended: not left or never verified, and with no end date in the past.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $query) {
                $query->whereNull('to')->orWhereDate('to', '>=', Carbon::today());
            })
            ->whereDoesntHave('modelState.state', function (Builder $query) {
                $query->whereIn('slug', self::ENDED_STATES);
            });
    }

    /**
     * The in-memory equivalent of scopeCurrent(), for affiliations already loaded with their state.
     */
    public function isCurrent(): bool
    {
        return !$this->hasEnded()
            && ($this->to === null || Carbon::parse($this->to)->gte(Carbon::today()));
    }

    public function hasEnded(): bool
    {
        return $this->isInAnyState(self::ENDED_STATES);
    }

    /**
     * Once an Organisation has affiliated the user, or the affiliation has ended, it is history.
     * Only its end date can change after that, through the leaver flow.
     */
    public function isEditable(): bool
    {
        return !$this->isInAnyState([State::STATE_AFFILIATION_APPROVED, ...self::ENDED_STATES]);
    }

    /**
     * Only a fresh affiliation the user added by mistake can be deleted: never affiliated,
     * never ended, and not on any project.
     */
    public function isDeletable(): bool
    {
        return $this->isEditable() && !$this->projectUsers()->exists();
    }

    private function isInAnyState(array $slugs): bool
    {
        return in_array($this->getState(), $slugs, true);
    }
}
