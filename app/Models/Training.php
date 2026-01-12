<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

/**
 *
 *
 * @OA\Schema (
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
 *          example="2024-02-04"
 *      ),
 *      @OA\Property(property="expires_at",
 *          type="string",
 *          example="2026-02-04"
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
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $provider
 * @property string $awarded_at
 * @property string $expires_at
 * @property int $expires_in_years
 * @property string $training_name
 * @property int|null $certification_id
 * @property int $pro_registration
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training applySorting()
 * @method static \Database\Factories\TrainingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereAwardedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereCertificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereExpiresInYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereProRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereTrainingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereUpdatedAt($value)
 * @mixin \Eloquent
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

    protected $casts = [
        'awarded_at' => 'date',
        'expires_at' => 'date',
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

    public function scopeExpiresToday($query)
    {
        return $query->whereRaw('DATE(expires_at) = CURDATE()');
    }
}
