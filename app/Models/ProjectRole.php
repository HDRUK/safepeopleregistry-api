<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

class ProjectRole extends Model
{
    use HasFactory;
    use SearchManager;

    protected $table = 'project_roles';

    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    protected static array $searchableColumns = [
        'name',
    ];

    protected static array $sortableColumns = [
        'name',
    ];

    public const PROJECT_ROLES = [
        'Principal Investigator (PI)',
        'Co-Investigator (Co-I) / Sub-Investigator (Sub-I)',
        'Data Analyst',
        'Data Engineer',
        'Postdoc',
        'Research Fellow',
        'Researcher',
        'Student',
    ];
}
