<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

/**
 * @OA\Schema(
 *     schema="Education",
 *     type="object",
 *     title="Education",
 *     description="Model representing education records",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the education record"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         example="Bachelor of Science in Computer Science",
 *         description="Title of the education qualification"
 *     ),
 *     @OA\Property(
 *         property="from",
 *         type="string",
 *         format="date",
 *         example="2015-09-01",
 *         description="Start date of the education qualification"
 *     ),
 *     @OA\Property(
 *         property="to",
 *         type="string",
 *         format="date",
 *         example="2019-06-30",
 *         description="End date of the education qualification"
 *     ),
 *     @OA\Property(
 *         property="institute_name",
 *         type="string",
 *         example="University of Example",
 *         description="Name of the educational institute"
 *     ),
 *     @OA\Property(
 *         property="institute_address",
 *         type="string",
 *         example="123 University Lane, Example City",
 *         description="Address of the educational institute"
 *     ),
 *     @OA\Property(
 *         property="institute_identifier",
 *         type="string",
 *         example="https://ror.org/12345",
 *         description="Identifier for the educational institute"
 *     ),
 *     @OA\Property(
 *         property="source",
 *         type="string",
 *         example="Self-reported",
 *         description="Source of the education record"
 *     ),
 *     @OA\Property(
 *         property="registry_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the registry associated with the education record"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the education record was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the education record was last updated"
 *     )
 * )
 * 
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string $from
 * @property string $to
 * @property string $institute_name
 * @property string|null $institute_address
 * @property string|null $institute_identifier
 * @property string|null $source
 * @property int $registry_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereInstituteAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereInstituteIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereInstituteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Education extends Model
{
    use HasFactory;
    use SearchManager;

    public $table = 'educations';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'from',
        'to',
        'institute_name',
        'institute_address',
        'institute_identifier',
        'source',
        'registry_id',
    ];

    protected static array $searchableColumns = [
        'title',
        'institute_name',
    ];

    protected static array $sortableColumns = [
        'title',
        'institute_name',
    ];
}
