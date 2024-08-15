<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    public $table = 'educations';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'from',
        'to',
        'institute_name',
        'institute_address',
        'institute_identifier',
        'source',
        'registry_id',
    ];
}
