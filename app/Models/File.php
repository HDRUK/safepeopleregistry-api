<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([FileObserver::class])]
class File extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'files';

    /**
     * Whether or not this model supports timestamps
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * What fields of this model are accepted as parameters
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'path',
        'status',
    ];
}
