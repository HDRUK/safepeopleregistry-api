<?php

namespace App\Models;

use App\Observers\FileObserver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([FileObserver::class])]
class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'type',
        'path',
        'status',
    ];
}
