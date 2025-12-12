<?php

namespace App\Models;

use App\Traits\StateWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustodianHasProjectHasSponsorship extends Model
{
    use StateWorkflow;

    protected $table = 'custodian_has_project_has_sponsorships';

    public $timestamps = true;

    protected $fillable = [
        'project_has_sponsorship_id',
        'custodian_id',
    ];

    protected static array $transitions = [
        State::STATE_SPONSORSHIP_PENDING => [
            State::STATE_SPONSORSHIP_PENDING,
            State::STATE_SPONSORSHIP_APPROVED,
            State::STATE_SPONSORSHIP_REJECTED,
        ],

        State::STATE_SPONSORSHIP_APPROVED => [],
        State::STATE_SPONSORSHIP_REJECTED => [],
    ];

    /**
     * Get the project sponsorship
     */
    public function projectHasSponsorships(): BelongsTo
    {
        return $this->belongsTo(ProjectHasSponsorship::class, 'project_has_sponsorship_id');
    }

    /**
     * Get the custodian
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id');
    }

    public function modelState(): MorphOne
    {
        return $this->morphOne(ModelState::class, 'stateable');
    }

    public static function getTransitions(): array
    {
        return static::$transitions;
    }

}
