<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *  @OA\Schema(
 *     schema="EntityModel",
 *     type="object",
 *     title="EntityModel",
 *     description="Model representing entity models",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the entity model"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Data Processing Model",
 *         description="Name of the entity model"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="This model processes data based on predefined rules.",
 *         description="Description of the entity model"
 *     ),
 *     @OA\Property(
 *         property="entity_model_type_id",
 *         type="integer",
 *         example=2,
 *         description="ID of the entity model type associated with this model"
 *     ),
 *     @OA\Property(
 *         property="calls_file",
 *         type="boolean",
 *         example=true,
 *         description="Indicates whether the model calls a file"
 *     ),
 *     @OA\Property(
 *         property="file_path",
 *         type="string",
 *         example="/path/to/file",
 *         description="Path to the file called by the model"
 *     ),
 *     @OA\Property(
 *         property="calls_operation",
 *         type="boolean",
 *         example=false,
 *         description="Indicates whether the model calls an operation"
 *     ),
 *     @OA\Property(
 *         property="operation",
 *         type="string",
 *         example="processData",
 *         description="Operation called by the model"
 *     ),
 *     @OA\Property(
 *         property="active",
 *         type="integer",
 *         example=1,
 *         description="Indicates whether the model is active (1 for active, 0 for inactive)"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the entity model was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the entity model was last updated"
 *     )
 * )
 * 
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string|null $description
 * @property int $entity_model_type_id
 * @property bool $calls_file
 * @property string|null $file_path
 * @property bool $calls_operation
 * @property string|null $operation
 * @property int $active
 * @property-read \App\Models\CustodianModelConfig|null $custodianModelConfig
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereCallsFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereCallsOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereEntityModelTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EntityModel extends Model
{
    use HasFactory;
    use SearchManager;

    protected $table = 'entity_models';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'entity_model_type_id',
        'calls_file',
        'file_path',
        'calls_operation',
        'operation',
        'active',
    ];

    protected $casts = [
        'calls_file' => 'boolean',
        'calls_operation' => 'boolean',
    ];

    protected $hidden = [
        'file_path',
        'operation',
    ];

    protected static array $searchableColumns = [
        'name',
    ];

    protected static array $sortableColumns = [
        'name',
    ];

    /**
     * Get the custodian model configuration associated with this entity model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\CustodianModelConfig>
     */
    public function custodianModelConfig(): HasOne
    {
        return $this->hasOne(CustodianModelConfig::class);
    }
}
