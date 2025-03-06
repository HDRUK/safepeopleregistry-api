<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

/**
 * @OA\Schema(
 *  schema="ProjectRole",
 *  title="ProjectRole",
 *  description="ProjectRole model",
 *  @OA\Property(property="id",
 *      type="integer",
 *      example=1
 *  ),
 *  @OA\Property(property="created_at",
 *      type="string",
 *      example="2023-10-10T15:03:00Z"
 *  ),
 *  @OA\Property(property="updated_at",
 *      type="string",
 *      example="2023-10-10T15:03:00Z"
 *  ),
 *  @OA\Property(property="name",
 *      type="string",
 *      example="Role Name"
 *  )
 * )
 */
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
