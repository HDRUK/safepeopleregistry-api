<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\ActionManager;
use App\Traits\FilterManager;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *
 *
 * @OA\Schema (
 *      schema="Custodian",
 *      title="Custodian",
 *      description="Custodian model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example=1,
 *          description="Model primary key"
 *      ),
 *      @OA\Property(property="created_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="updated_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="name",
 *          type="string",
 *          example="Custodian Name"
 *      ),
 *      @OA\Property(property="unique_identifier",
 *          type="string",
 *          example="aVl9jMgJHQjZz0xMOJ56hNyJJm9nIjm51TSf7Wp6",
 *          description="A unique identifier for Custodian's within SOURSD"
 *      ),
 *      @OA\Property(property="contact_email",
 *          type="string",
 *          example="key.contact.email@email.com"
 *      ),
 *      @OA\Property(property="enabled",
 *          type="bool",
 *          example="true"
 *      ),
 *      @OA\Property(property="invite_accepted_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="invite_sent_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="idvt_required",
 *          type="bool",
 *          example="false"
 *      ),
 *      @OA\Property(property="gateway_app_id",
 *          type="string",
 *          example="HfjmY6rOymGjQwGcPkXIghYOggDcV1A83no4pbZp"
 *      ),
 *      @OA\Property(property="gateway_client_id",
 *          type="string",
 *          example="RTWSlsx8iuQxN6JmfKFkopyWF8wfeKNt4tkuJcS3"
 *      ),
 *      @OA\Property(property="client_id",
 *          type="string",
 *          example="1111-2222-3333-4444-5555"
 *      )
 * )
 */
class Custodian extends Model
{
    use HasFactory;
    use SearchManager;
    use ActionManager;
    use FilterManager;

    protected $table = 'custodians';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'unique_identifier',
        'calculated_hash',
        'contact_email',
        'enabled',
        'invite_accepted_at',
        'invite_sent_at',
        'idvt_required',
        'gateway_app_id',
        'gateway_client_id',
        'client_id',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'idvt_required' => 'boolean',
    ];

    protected $hidden = [
        'calculated_hash',
    ];

    public const ACTION_COMPLETE_CONFIGURATION = 'complete_configuration';
    public const ACTION_ADD_CONTACTS = 'add_contacts_completed';
    public const ACTION_ADD_USERS = 'add_users_completed';
    public const ACTION_ADD_PROJECTS = 'add_projects_completed';
    public const ACTION_APPROVE_AN_ORGANISATION = 'approve_an_organisation_completed';

    protected static array $defaultActions = [
        self::ACTION_COMPLETE_CONFIGURATION,
        self::ACTION_ADD_CONTACTS,
        self::ACTION_ADD_USERS,
        self::ACTION_ADD_PROJECTS,
        self::ACTION_APPROVE_AN_ORGANISATION,
    ];

    protected static array $searchableColumns = [
        'name',
        'contact_email',
    ];

    protected static array $sortableColumns = [
        'name',
        'contact_email',
    ];

    /**
     * Get all rules that belong to this custodian.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\DecisionModel>
     */
    public function rules(): BelongsToMany
    {
        // LS - TODO - this needs renaming.
        return $this->belongsToMany(DecisionModel::class, 'custodian_has_rules', 'custodian_id', 'rule_id');
    }

    /**
     * Get all custodian users that belong to this custodian.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\CustodianUser>
     */
    public function custodianUsers()
    {
        return $this->hasMany(CustodianUser::class, 'custodian_id', 'id');
    }

    /**
     * Get all custodian users that belong to this custodian.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Project>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_has_custodians', 'custodian_id', 'project_id');
    }


    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\CustodianHasValidationCheck>
     */
    public function validationChecks(): BelongsToMany
    {
        return $this->belongsToMany(
            ValidationCheck::class,
            'custodian_has_validation_check'
        )
            ->using(CustodianHasValidationCheck::class);
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\CustodianHasProjectOrganisation>
     */
    public function projectOrganisations(): HasMany
    {
        return $this->hasMany(CustodianHasProjectOrganisation::class, 'custodian_id');
    }
}
