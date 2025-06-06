<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
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

    public $timestamp = false;

    protected $fillable = [
        'training_id',
        'file_id',
    ];
}
