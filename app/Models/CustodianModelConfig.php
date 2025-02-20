<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="CustodianModelConfig",
 *      title="CustodianModelConfig",
 *      description="CustodianModelConfig model",
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
 *          example="2023-10-10T18:03:00Z"
 *      ),
 *      @OA\Property(property="entity_model_id",
 *          type="integer",
 *          example=1
 *      ),
 *      @OA\Property(property="active",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(property="custodian_id",
 *          type="integer",
 *          example=12
 *      ),
 * )
 */
class CustodianModelConfig extends Model
{
    use HasFactory;

    protected $table = 'custodian_model_configs';

    public $timestamp = true;

    protected $fillable = [
        'entity_model_id',
        'active',
        'custodian_id',
    ];
}
