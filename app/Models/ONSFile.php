<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $path
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ONSFile extends Model
{
    use HasFactory;

    public $table = 'ons_files';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'path',
        'status',
    ];
}
