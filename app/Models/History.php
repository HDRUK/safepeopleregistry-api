<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 *  @OA\Schema(
 *     schema="History",
 *     type="object",
 *     title="History",
 *     description="Model representing historical records",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the history record"
 *     ),
 *     @OA\Property(
 *         property="affiliation_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the affiliation associated with the history record"
 *     ),
 *     @OA\Property(
 *         property="endorsement_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the endorsement associated with the history record"
 *     ),
 *     @OA\Property(
 *         property="infringement_id",
 *         type="integer",
 *         example=12,
 *         description="ID of the infringement associated with the history record"
 *     ),
 *     @OA\Property(
 *         property="project_id",
 *         type="integer",
 *         example=100,
 *         description="ID of the project associated with the history record"
 *     ),
 *     @OA\Property(
 *         property="access_key_id",
 *         type="integer",
 *         example=5,
 *         description="ID of the access key associated with the history record"
 *     ),
 *     @OA\Property(
 *         property="custodian_identifier",
 *         type="string",
 *         example="CUST12345",
 *         description="Identifier for the custodian associated with the history record"
 *     ),
 *     @OA\Property(
 *         property="ledger_hash",
 *         type="string",
 *         example="abc123hash",
 *         description="Hash of the ledger associated with the history record"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the history record was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the history record was last updated"
 *     )
 * )
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
