<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

/**
 * @OA\Schema(
 *      schema="Training",
 *      title="Training",
 *      description="Training model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example="123"
 *      ),
 *      @OA\Property(property="created_at",
 *          type="string",
 *          example="2024-02-04 12:00:00"
 *      ),
 *      @OA\Property(property="updated_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(property="registry_id",
 *          type="integer",
 *          example="1"
 *      ),
 *      @OA\Property(property="provider",
 *          type="string",
 *          example="ONS"
 *      ),
 *      @OA\Property(property="awarded_at",
 *          type="string",
 *          example="2024-02-04 12:10:00"
 *      ),
 *      @OA\Property(property="expires_at",
 *          type="string",
 *          example="2026-02-04 12:09:59"
 *      ),
 *      @OA\Property(property="expires_in_years",
 *          type="integer",
 *          example="2"
 *      ),
 *      @OA\Property(property="training_name",
 *          type="string",
 *          example="Safe Researcher Training"
 *      ),
 *      @OA\Property(property="pro_registration",
 *          type="integer",
 *          example="1"
 *      )
 * )
 */
class Training extends Model
{
    use HasFactory;
    use SearchManager;

    public $timestamps = true;

    protected $table = 'trainings';

    protected $fillable = [
        'provider',
        'awarded_at',
        'expires_at',
        'expires_in_years',
        'training_name',
        'certification_id',
        'pro_registration',
    ];

    protected static array $searchableColumns = [
        'provider',
        'training_name',
    ];

    protected static array $sortableColumns = [
        'provider',
        'training_name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\File>
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(
            File::class,
            'training_has_files',
            'training_id',
            'file_id'
        );
    }
}
