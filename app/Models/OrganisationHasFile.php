<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OrganisationHasFile",
 *     type="object",
 *     title="OrganisationHasFile",
 *     description="Pivot model representing the relationship between organisations and files",
 *     @OA\Property(
 *         property="organisation_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the organisation"
 *     ),
 *     @OA\Property(
 *         property="file_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the file"
 *     )
 * )
 * 
 * @property int $organisation_id
 * @property int $file_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile whereOrganisationId($value)
 * @mixin \Eloquent
 */
class OrganisationHasFile extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_files';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'file_id',
    ];
}
