<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingHasFile extends Model
{
    protected $table = 'training_has_files';

    public $timestamp = false;

    protected $fillable = [
        'training_id',
        'file_id',
    ];
}
