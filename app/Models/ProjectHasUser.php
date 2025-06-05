<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHasUser extends Model
{
    use HasFactory;

    public $incrementing = false;
    //protected $primaryKey = null;

    protected $table = 'project_has_users';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'user_digital_ident',
        'project_role_id',
        'primary_contact',
        'affiliation_id',
    ];

    public static function defaultValidationChecks(): array
    {
        return [
            [
                'name' => 'mandatory_training_complete',
                'description' => 'Mandatory training has been completed',
            ],
            [
                'name' => 'no_misconduct',
                'description' => 'The user has no record of misconduct',
            ],
            [
                'name' => 'no_relevant_criminal_record',
                'description' => 'The user has no relevant criminal record',
            ],
            [
                'name' => 'organisation_has_confirmed_the_user',
                'description' => 'The organisation has confirmed the user',
            ],
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(ProjectRole::class, 'project_role_id', 'id');
    }

    public function registry(): BelongsTo
    {
        return $this->belongsTo(Registry::class, 'user_digital_ident', 'digi_ident');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function affiliation(): BelongsTo
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id', 'id');
    }

    public function approvals(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProjectHasCustodian::class,
            Project::class,
            'id',
            'id',
            'project_id',
            'project_id'
        )->where('approved', true);
    }
}
