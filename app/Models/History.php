<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $affiliation_id
 * @property int|null $endorsement_id
 * @property int|null $infringement_id
 * @property int|null $project_id
 * @property int|null $access_key_id
 * @property string|null $custodian_identifier
 * @property string $ledger_hash
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Endorsement> $endorsements
 * @property-read int|null $endorsements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Infringement> $infringements
 * @property-read int|null $infringements_count
 * @method static \Database\Factories\HistoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereAccessKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereAffiliationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereCustodianIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereEndorsementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereInfringementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereLedgerHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class History extends Model
{
    use HasFactory;

    protected $table = 'histories';

    public $timestamps = true;

    protected $fillable = [
        'affiliation_id',
        'endorsement_id',
        'infringement_id',
        'project_id',
        'access_key_id',
        'custodian_identifier',
        'ledger_hash',
    ];

    /**
     * Get the endorsements associated with this history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Endorsement>
     */
    public function endorsements(): BelongsToMany
    {
        return $this->belongsToMany(
            Endorsement::class,
            'history_has_endorsements',
        );
    }

    /**
     * Get the infringements associated with this history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Infringement>
     */
    public function infringements(): BelongsToMany
    {
        return $this->belongsToMany(
            Infringement::class,
            'history_has_infringements',
        );
    }
}
