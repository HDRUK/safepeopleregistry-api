<?php

namespace App\Models;

use App\Observers\ONSFileObserver;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ONSFileObserver::class])]
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
