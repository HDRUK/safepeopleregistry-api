<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RegistryHasFile",
 *     type="object",
 *     title="RegistryHasFile",
 *     description="Pivot model representing the relationship between registries and files",
 *     @OA\Property(
 *         property="registry_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the registry"
 *     ),
 *     @OA\Property(
 *         property="file_id",
 *         type="string",
 *         example="file_123",
 *         description="ID of the file"
 *     )
 * )
 * 
 * @property int $registry_id
 * @property string $file_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile whereRegistryId($value)
 * @mixin \Eloquent
 */
class RegistryHasFile extends Model
{
    use HasFactory;

    protected $table = 'registry_has_files';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'file_id',
    ];
}
