<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Traits\StateWorkflow;

/**
 *
 *
 * @property int $organisation_id
 * @property int $custodian_id
 * @property int $approved
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Custodian|null $custodian
 * @property-read \App\Models\Organisation|null $organisation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasProjectOrganisation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasProjectOrganisation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasProjectOrganisation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasProjectOrganisation whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasProjectOrganisation whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasProjectOrganisation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasProjectOrganisation whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasProjectOrganisation whereOrganisationId($value)
 * @mixin \Eloquent
 */
class CustodianHasProjectOrganisation extends Model
{
    use HasFactory;
    use StateWorkflow;

    protected array $transitions = [
        State::STATE_FORM_RECEIVED => [
            State::STATE_VALIDATION_IN_PROGRESS,
            State::STATE_MORE_ORG_INFO_REQ,
        ],
        State::STATE_VALIDATION_IN_PROGRESS => [
            State::STATE_VALIDATION_COMPLETE,
            State::STATE_MORE_ORG_INFO_REQ,
            State::STATE_ESCALATE_VALIDATION,
            State::STATE_VALIDATED,
        ],
        State::STATE_VALIDATION_COMPLETE => [
            State::STATE_ESCALATE_VALIDATION,
            State::STATE_VALIDATED,
        ],
        State::STATE_MORE_ORG_INFO_REQ => [
            State::STATE_ESCALATE_VALIDATION,
            State::STATE_VALIDATED,
        ],
        State::STATE_ESCALATE_VALIDATION => [
            State::STATE_VALIDATED,
        ],
        State::STATE_VALIDATED => [],
    ];

    public function getTransitions(): array
    {
        return $this->transitions;
    }

    protected $table = 'custodian_has_project_has_organisation';

    public $timestamps = false;

    protected $fillable = [
        'project_has_organisation_id',
        'custodian_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            if (in_array(StateWorkflow::class, class_uses($model))) {
                $model->setState(State::STATE_FORM_RECEIVED);
                $model->save();
            }
        });
    }


    /**
     * Get the organisation associated with the approval.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ProjectHasOrganisation>
     */
    public function projectOrganisation(): BelongsTo
    {
        return $this->belongsTo(ProjectHasOrganisation::class, 'project_has_organisation_id');
    }

    /**
     * Get the custodian associated with the approval.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Custodian>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id');
    }

    public function modelState(): MorphOne
    {
        return $this->morphOne(ModelState::class, 'stateable');
    }
}
