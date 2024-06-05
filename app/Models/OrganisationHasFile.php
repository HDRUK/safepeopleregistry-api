<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationHasFile extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_files';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'file_id',
    ];
}
