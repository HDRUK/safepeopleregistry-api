<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

class Education extends Model
{
    use HasFactory;
    use SearchManager;

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

    protected static array $searchableColumns = [
        'title',
        'institute_name',
    ];

    protected static array $sortableColumns = [
        'title',
        'institute_name',
    ];
}
