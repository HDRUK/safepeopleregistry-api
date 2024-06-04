<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistryHasFile extends Model
{
    use HasFactory;

    protected $table = 'registry_has_files';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'file_id',
    ];
}
