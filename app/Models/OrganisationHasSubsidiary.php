<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="OrganisationHasSubsidiary",
 *     type="object",
 *     title="OrganisationHasSubsidiary",
 *     description="Pivot model representing the relationship between organisations and subsidiaries",
 *     @OA\Property(
 *         property="organisation_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the organisation"
 *     ),
 *     @OA\Property(
 *         property="subsidiary_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the subsidiary"
 *     )
 * )
 *
 * @property int $organisation_id
 * @property int $subsidiary_id
 * @property-read \App\Models\Organisation|null $organisation
 * @property-read \App\Models\Subsidiary|null $subsidiary
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary whereSubsidiaryId($value)
 * @mixin \Eloquent
 */
class OrganisationHasSubsidiary extends Model
{
    use HasFactory;

    public $table = 'organisation_has_subsidiaries';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'subsidiary_id',
    ];

    /**
     * Get the organisation associated with this record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Organisation>
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    /**
     * Get the subsidiary associated with this record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Subsidiary>
     */
    public function subsidiary(): BelongsTo
    {
        return $this->belongsTo(Subsidiary::class, 'subsidiary_id');
    }
}
