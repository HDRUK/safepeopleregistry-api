<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TrainingHasFile",
 *     type="object",
 *     title="TrainingHasFile",
 *     description="Pivot model representing the relationship between trainings and files",
 *     @OA\Property(
 *         property="training_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the training"
 *     ),
 *     @OA\Property(
 *         property="file_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the file"
 *     )
 * )
 * 
 * @property int $training_id
 * @property int $file_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile whereTrainingId($value)
 * @mixin \Eloquent
 */
class TrainingHasFile extends Model
{
    protected $table = 'training_has_files';

    public $timestamps = false;

    protected $fillable = [
        'training_id',
        'file_id',
    ];
}
