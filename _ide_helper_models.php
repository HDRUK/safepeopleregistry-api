<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $awarded_at
 * @property string $awarding_body_name
 * @property string|null $awarding_body_ror
 * @property string $title
 * @property string $expires_at
 * @property string $awarded_locale
 * @method static \Database\Factories\AccreditationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereAwardedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereAwardedLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereAwardingBodyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereAwardingBodyRor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereUpdatedAt($value)
 */
	class Accreditation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *     schema="ActionLog",
 *     title="Action Log",
 *     description="Action Log model",
 * 
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Model primary key"
 *     ),
 * 
 *     @OA\Property(
 *         property="entity_type",
 *         type="string",
 *         example="User",
 *         description="Type of the entity associated with the action log"
 *     ),
 * 
 *     @OA\Property(
 *         property="entity_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the entity associated with the action log"
 *     ),
 * 
 *     @OA\Property(
 *         property="action",
 *         type="string",
 *         example="Updated profile",
 *         description="Description of the action performed"
 *     ),
 * 
 *     @OA\Property(
 *         property="completed_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-10T15:43:00Z",
 *         description="Timestamp when the action was completed (nullable)"
 *     ),
 * )
 * @property int $id
 * @property \App\Enums\ActionLogType $entity_type
 * @property int $entity_id
 * @property string $action
 * @property string|null $completed_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $entity
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereId($value)
 */
	class ActionLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *      schema="Affiliation",
 *      title="Affiliation",
 *      description="Affiliation model",
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
 *          example="2023-10-10T15:43:00Z"
 *      ),
 *      @OA\Property(property="organisation_id",
 *          type="integer",
 *          example=1,
 *          description="Organisational link"
 *      ),
 *      @OA\Property(property="member_id",
 *          type="string",
 *          example="325987-skdjfh283429-lkfsfdh",
 *          description="Member ID UUID"
 *      ),
 *      @OA\Property(property="relationship",
 *          type="string",
 *          example="employee",
 *          description="Textual representation of affiliation relationship"
 *      ),
 *      @OA\Property(property="from",
 *          type="string",
 *          example="2023-01-12",
 *          description="Date affiliation commenced"
 *      ),
 *      @OA\Property(property="to",
 *          type="string",
 *          example="2024-12-01",
 *          description="Date affiliation concluded"
 *      ),
 *      @OA\Property(property="department",
 *          type="string",
 *          example="Research & Development",
 *          description="Department worked during affiliation"
 *      ),
 *      @OA\Property(property="role",
 *          type="string",
 *          example="Principal Investigator (PI)",
 *          description="Role held during affiliation"
 *      ),
 *      @OA\Property(property="email",
 *          type="string",
 *          example="user@domain.com",
 *          description="Professional email held during affiliation"
 *      ),
 *      @OA\Property(property="ror",
 *          type="string",
 *          example="0hgyr56",
 *          description="The ROR.org identifier for this affiliation institute"
 *      ),
 *      @OA\Property(property="registry_id",
 *          type="integer",
 *          example=123,
 *          description="The Registry primary key associated with this affiliation"
 *      )
 * )
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $organisation_id
 * @property string $member_id
 * @property string|null $relationship
 * @property string|null $from
 * @property string|null $to
 * @property string|null $department
 * @property string|null $role
 * @property string|null $email
 * @property string|null $ror
 * @property int $registry_id
 * @property int|null $verdict_user_id
 * @property string|null $verdict_date_actioned
 * @property int|null $verdict_outcome
 * @property-read mixed $registry_affiliation_state
 * @property-read \App\Models\Organisation|null $organisation
 * @property-read \App\Models\Registry|null $registry
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RegistryHasAffiliation> $registryHasAffiliations
 * @property-read int|null $registry_has_affiliations_count
 * @method static \Database\Factories\AffiliationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereRor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereVerdictDateActioned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereVerdictOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Affiliation whereVerdictUserId($value)
 */
	class Affiliation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $registration_id
 * @property string $name
 * @property string|null $website
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $town
 * @property string|null $county
 * @property string|null $country
 * @property string|null $postcode
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organisation> $organisations
 * @property-read int|null $organisations_count
 * @method static \Database\Factories\CharityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereRegistrationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereTown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereWebsite($value)
 */
	class Charity extends \Eloquent {}
}

namespace App\Models{
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
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $unique_identifier
 * @property string $calculated_hash
 * @property string $contact_email
 * @property bool $enabled
 * @property string|null $invite_accepted_at
 * @property string|null $invite_sent_at
 * @property bool $idvt_required
 * @property string|null $gateway_app_id
 * @property string|null $gateway_client_id
 * @property string $client_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionLog> $actionLogs
 * @property-read int|null $action_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organisation> $approvedOrganisations
 * @property-read int|null $approved_organisations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustodianUser> $custodianUsers
 * @property-read int|null $custodian_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Project> $projects
 * @property-read int|null $projects_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DecisionModel> $rules
 * @property-read int|null $rules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionLog> $secondaryActionLogs
 * @property-read int|null $secondary_action_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionLog> $tertiaryActionLogs
 * @property-read int|null $tertiary_action_logs_count
 * @property-read \App\Models\CustodianHasValidationCheck|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ValidationCheck> $validationChecks
 * @property-read int|null $validation_checks_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian applySorting()
 * @method static \Database\Factories\CustodianFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian filterByState()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereCalculatedHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereGatewayAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereGatewayClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereIdvtRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereInviteAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereInviteSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereUniqueIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Custodian whereUpdatedAt($value)
 */
	class Custodian extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $custodian_id
 * @property int $rule_id
 * @property-read \App\Models\Custodian $custodian
 * @property-read \App\Models\Rules $rule
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule whereRuleId($value)
 */
	class CustodianHasRule extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $custodian_id
 * @property int $validation_check_id
 * @property-read \App\Models\Custodian $custodian
 * @property-read \App\Models\ValidationCheck $validationCheck
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck whereValidationCheckId($value)
 */
	class CustodianHasValidationCheck extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *      schema="CustodianModelConfig",
 *      title="CustodianModelConfig",
 *      description="CustodianModelConfig model",
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
 *          example="2023-10-10T18:03:00Z"
 *      ),
 *      @OA\Property(property="entity_model_id",
 *          type="integer",
 *          example=1
 *      ),
 *      @OA\Property(property="active",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(property="custodian_id",
 *          type="integer",
 *          example=12
 *      ),
 * )
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $entity_model_id
 * @property int $active
 * @property int $custodian_id
 * @method static \Database\Factories\CustodianModelConfigFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianModelConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianModelConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianModelConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianModelConfig whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianModelConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianModelConfig whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianModelConfig whereEntityModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianModelConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianModelConfig whereUpdatedAt($value)
 */
	class CustodianModelConfig extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *      schema="CustodianUser",
 *      title="Custodian User",
 *      description="CustodianUser model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example=1,
 *          description="Model primary key"
 *      ),
 *      @OA\Property(property="custodian_id",
 *          type="integer",
 *          example=1,
 *          description="Custodian primary key"
 *      ),
 *      @OA\Property(property="created_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="updated_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="first_name",
 *          type="string",
 *          example="John"
 *      ),
 *      @OA\Property(property="last_name",
 *          type="string",
 *          example="Smith"
 *      ),
 *      @OA\Property(property="email",
 *          type="string",
 *          example="First name"
 *      )
 * )
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $password
 * @property string|null $provider
 * @property string|null $keycloak_id
 * @property int $custodian_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser applySorting()
 * @method static \Database\Factories\CustodianUserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereKeycloakId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereUpdatedAt($value)
 */
	class CustodianUser extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $custodian_user_id
 * @property int $permission_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission whereCustodianUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission wherePermissionId($value)
 */
	class CustodianUserHasPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $custodian_id
 * @property string $url
 * @property int $webhook_event
 * @property-read \App\Models\WebhookEventTrigger|null $eventTrigger
 * @method static \Database\Factories\CustodianWebhookReceiverFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereWebhookEvent($value)
 */
	class CustodianWebhookReceiver extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $class
 * @property string $log
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereUpdatedAt($value)
 */
	class DebugLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $model_type
 * @property string $conditions
 * @property string $rule_class
 * @property string $description
 * @property int $entity_model_type_id
 * @property-read \App\Models\CustodianModelConfig|null $custodianModelConfig
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereEntityModelTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereRuleClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereUpdatedAt($value)
 */
	class DecisionModel extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string|null $category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string $from
 * @property string $to
 * @property string $institute_name
 * @property string|null $institute_address
 * @property string|null $institute_identifier
 * @property string|null $source
 * @property int $registry_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereInstituteAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereInstituteIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereInstituteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereUpdatedAt($value)
 */
	class Education extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $reported_by
 * @property string|null $comment
 * @property int $raised_against
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereRaisedAgainst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereReportedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereUpdatedAt($value)
 */
	class Endorsement extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string|null $description
 * @property int $entity_model_type_id
 * @property bool $calls_file
 * @property string|null $file_path
 * @property bool $calls_operation
 * @property string|null $operation
 * @property int $active
 * @property-read \App\Models\CustodianModelConfig|null $custodianModelConfig
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereCallsFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereCallsOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereEntityModelTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModel whereUpdatedAt($value)
 */
	class EntityModel extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType whereUpdatedAt($value)
 */
	class EntityModelType extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $project_id
 * @property string $from
 * @property string $to
 * @property int $organisation_id
 * @method static \Database\Factories\ExperienceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereUpdatedAt($value)
 */
	class Experience extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $type
 * @property string $path
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereUpdatedAt($value)
 */
	class File extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $affiliation_id
 * @property int|null $endorsement_id
 * @property int|null $infringement_id
 * @property int|null $project_id
 * @property int|null $access_key_id
 * @property string|null $custodian_identifier
 * @property string $ledger_hash
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Endorsement> $endorsements
 * @property-read int|null $endorsements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Infringement> $infringements
 * @property-read int|null $infringements_count
 * @method static \Database\Factories\HistoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereAccessKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereAffiliationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereCustodianIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereEndorsementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereInfringementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereLedgerHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|History whereUpdatedAt($value)
 */
	class History extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $function
 * @property string $args
 * @property string $config
 * @property int $enabled
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereArgs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereFunction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereUpdatedAt($value)
 */
	class IDVTPlugin extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $registry_id
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $town
 * @property string|null $county
 * @property string|null $country
 * @property string|null $postcode
 * @property string|null $dob
 * @property string|null $idvt_completed_at
 * @property string|null $idvt_result_text
 * @property string|null $idvt_context
 * @property int $idvt_success
 * @property string|null $idvt_identification_number
 * @property string|null $idvt_document_type
 * @property string|null $idvt_document_number
 * @property string|null $idvt_document_country
 * @property string|null $idvt_document_valid_until
 * @property string|null $idvt_attempt_id
 * @property string|null $idvt_context_id
 * @property string|null $idvt_document_dob
 * @property string|null $idvt_started_at
 * @method static \Database\Factories\IdentityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtAttemptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtContextId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtIdentificationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtResultText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereTown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity withoutTrashed()
 */
	class Identity extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $reported_by
 * @property string|null $comment
 * @property int $raised_against
 * @method static \Database\Factories\InfringementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereRaisedAgainst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereReportedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereUpdatedAt($value)
 */
	class Infringement extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $infringement_id
 * @property int $resolution_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution whereInfringementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution whereResolutionId($value)
 */
	class InfringementHasResolution extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property-read \App\Models\State $state
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $state_id
 * @property string $stateable_type
 * @property int $stateable_id
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $stateable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereStateableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereStateableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereUpdatedAt($value)
 */
	class ModelState extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $path
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereUpdatedAt($value)
 */
	class ONSFile extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *      schema="Organisation",
 *      title="Organisation",
 *      description="Organisation model",
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
 *          example="2023-10-10T16:03:00Z"
 *      ),
 *      @OA\Property(property="organisation_name",
 *          type="string",
 *          example="An Organisation Ltd"
 *      ),
 *      @OA\Property(property="address_1",
 *          type="string",
 *          example="123 Organisation Road"
 *      ),
 *      @OA\Property(property="address_2",
 *          type="string",
 *          example="Other address line"
 *      ),
 *      @OA\Property(property="town",
 *          type="string",
 *          example="Town"
 *      ),
 *      @OA\Property(property="county",
 *          type="string",
 *          example="County"
 *      ),
 *      @OA\Property(property="country",
 *          type="string",
 *          example="Country"
 *      ),
 *      @OA\Property(property="postcode",
 *          type="string",
 *          example="Po5t c0de"
 *      ),
 *      @OA\Property(property="lead_applicant_organisation_name",
 *          type="string",
 *          example="Lead Applicant Organisation"
 *      ),
 *      @OA\Property(property="lead_applicant_email",
 *          type="string",
 *          example="lead.applicant@email.com"
 *      ),
 *      @OA\Property(property="organisation_unique_id",
 *          type="string",
 *          example="ghyt843lgfk-akdgfskjh"
 *      ),
 *      @OA\Property(property="applicant_names",
 *          type="string",
 *          example="Applicant One, Applicant Two"
 *      ),
 *      @OA\Property(property="funders_and_sponsors",
 *          type="string",
 *          example="Funder Org. Sponsor Org"
 *      ),
 *      @OA\Property(property="sub_license_arrangements",
 *          type="string",
 *          example="Sub-license arrangements..."
 *      ),
 *      @OA\Property(property="verified",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(property="dsptk_ods_code",
 *          type="string",
 *          example="8HQ90"
 *      ),
 *      @OA\Property(property="dsptk_certified",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(property="dsptk_expiry_date",
 *          type="string",
 *          example="2026-12-01"
 *      ),
 *      @OA\Property(property="iso_27001_certified",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(property="iso_27001_certification_num",
 *          type="string",
 *          example="NUM1234"
 *      ),
 *      @OA\Property(property="iso_expiry_date",
 *          type="string",
 *          example="2026-12-01"
 *      ),
 *      @OA\Property(property="ce_certified",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(property="ce_certification_num",
 *          type="string",
 *          example="NUM1234"
 *      ),
 *      @OA\Property(property="ce_expiry_date",
 *          type="string",
 *          example="2026-12-01"
 *      ),
 *      @OA\Property(property="ce_plus_certified",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(property="ce_plus_certification_num",
 *          type="string",
 *          example="NUM1234"
 *      ),
 *      @OA\Property(property="ce_plus_expiry_date",
 *          type="string",
 *          example="2026-12-01"
 *      ),
 *      @OA\Property(property="idvt_result",
 *          type="integer",
 *          example=1
 *      ),
 *      @OA\Property(property="idvt_result_perc",
 *          type="integer",
 *          example=100
 *      ),
 *      @OA\Property(property="idvt_errors",
 *          type="string",
 *          example="Verification failed for XYZ reason"
 *      ),
 *      @OA\Property(property="idvt_completed_at",
 *          type="string",
 *          example="2023-10-10T16:03:00Z"
 *      ),
 *      @OA\Property(property="companies_house_no",
 *          type="string",
 *          example="10887014"
 *      ),
 *      @OA\Property(property="sector_id",
 *          type="integer",
 *          example=1
 *      ),
 *      @OA\Property(property="ror_id",
 *          type="string",
 *          example="02wnqcb97",
 *          description="ROR.org identification for Research Organisations"
 *      ),
 *      @OA\Property(property="website",
 *          type="string",
 *          example="https://yourdomain.com"
 *      ),
 *      @OA\Property(property="smb_status",
 *          type="boolean",
 *          example="false",
 *          description="Declaration of small/medium business"
 *      ),
 *      @OA\Property(property="organisation_size",
 *          type="integer",
 *          example="1",
 *          description="Organisation size. Integer denotes list index rather than absolute value"
 *      ),
 *      @OA\Property(property="unclaimed",
 *          type="boolean",
 *          example="false",
 *          description="Unclaimed"
 *      ),
 * )
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $organisation_name
 * @property string $address_1
 * @property string|null $address_2
 * @property string $town
 * @property string $county
 * @property string $country
 * @property string $postcode
 * @property string|null $lead_applicant_organisation_name
 * @property string|null $lead_applicant_email
 * @property string|null $password
 * @property string $organisation_unique_id
 * @property string|null $applicant_names
 * @property string|null $funders_and_sponsors
 * @property string|null $sub_license_arrangements
 * @property bool $verified
 * @property string|null $dsptk_ods_code
 * @property int $dsptk_certified
 * @property \Illuminate\Support\Carbon|null $dsptk_expiry_date
 * @property int|null $dsptk_expiry_evidence
 * @property bool $iso_27001_certified
 * @property bool $ce_certified
 * @property string|null $ce_certification_num
 * @property \Illuminate\Support\Carbon|null $ce_expiry_date
 * @property int|null $ce_expiry_evidence
 * @property int $ce_plus_certified
 * @property string|null $ce_plus_certification_num
 * @property \Illuminate\Support\Carbon|null $ce_plus_expiry_date
 * @property int|null $ce_plus_expiry_evidence
 * @property bool|null $idvt_result
 * @property float|null $idvt_result_perc
 * @property string|null $idvt_errors
 * @property string|null $idvt_completed_at
 * @property string $companies_house_no
 * @property int $sector_id
 * @property string|null $iso_27001_certification_num
 * @property \Illuminate\Support\Carbon|null $iso_expiry_date
 * @property int|null $iso_expiry_evidence
 * @property string|null $ror_id
 * @property string|null $website
 * @property int|null $smb_status
 * @property int|null $organisation_size
 * @property bool $unclaimed
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionLog> $actionLogs
 * @property-read int|null $action_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Affiliation> $affiliations
 * @property-read int|null $affiliations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Custodian> $approvals
 * @property-read int|null $approvals_count
 * @property-read \App\Models\File|null $ceExpiryEvidence
 * @property-read \App\Models\File|null $cePlusExpiryEvidence
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Charity> $charities
 * @property-read int|null $charities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $delegates
 * @property-read int|null $delegates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Department> $departments
 * @property-read int|null $departments_count
 * @property-read \App\Models\File|null $dsptkExpiryEvidence
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @property-read mixed $evaluation
 * @property-read \App\Models\File|null $isoExpiryEvidence
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $latestEvidence
 * @property-read int|null $latest_evidence_count
 * @property-read \App\Models\ModelState|null $modelState
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Project> $projects
 * @property-read int|null $projects_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Registry> $registries
 * @property-read int|null $registries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionLog> $secondaryActionLogs
 * @property-read int|null $secondary_action_logs_count
 * @property-read \App\Models\Sector|null $sector
 * @property-read \App\Models\User|null $sroOfficer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subsidiary> $subsidiaries
 * @property-read int|null $subsidiaries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionLog> $tertiaryActionLogs
 * @property-read int|null $tertiary_action_logs_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation applySorting()
 * @method static \Database\Factories\OrganisationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation filterByState()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation getCurrentRegistries($id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation getOrganisationsProjects()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereApplicantNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCeCertificationNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCeCertified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCeExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCeExpiryEvidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCePlusCertificationNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCePlusCertified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCePlusExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCePlusExpiryEvidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCompaniesHouseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereDsptkCertified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereDsptkExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereDsptkExpiryEvidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereDsptkOdsCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereFundersAndSponsors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereIdvtCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereIdvtErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereIdvtResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereIdvtResultPerc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereIso27001CertificationNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereIso27001Certified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereIsoExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereIsoExpiryEvidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereLeadApplicantEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereLeadApplicantOrganisationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereOrganisationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereOrganisationSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereOrganisationUniqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereRorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereSectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereSmbStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereSubLicenseArrangements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereTown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereUnclaimed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereWebsite($value)
 */
	class Organisation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $first_name
 * @property string $last_name
 * @property int $is_dpo
 * @property int $is_hr
 * @property string $email
 * @property int $priority_order
 * @property int $organisation_id
 * @method static \Database\Factories\OrganisationDelegateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate whereIsDpo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate whereIsHr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate wherePriorityOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationDelegate whereUpdatedAt($value)
 */
	class OrganisationDelegate extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $charity_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity whereOrganisationId($value)
 */
	class OrganisationHasCharity extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $organisation_id
 * @property int $custodian_id
 * @property int $approved
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Custodian|null $custodian
 * @property-read \App\Models\Organisation|null $organisation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereOrganisationId($value)
 */
	class OrganisationHasCustodianApproval extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $organisation_id
 * @property int $permission_id
 * @property int $custodian_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission wherePermissionId($value)
 */
	class OrganisationHasCustodianPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $organisation_id
 * @property int $department_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment whereOrganisationId($value)
 */
	class OrganisationHasDepartment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $organisation_id
 * @property int $file_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile whereOrganisationId($value)
 */
	class OrganisationHasFile extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $organisation_id
 * @property int $subsidiary_id
 * @property-read \App\Models\Organisation|null $organisation
 * @property-read \App\Models\Subsidiary|null $subsidiary
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasSubsidiary whereSubsidiaryId($value)
 */
	class OrganisationHasSubsidiary extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int|null $organisation_id
 * @property string $status
 * @property string|null $invite_accepted_at
 * @property string|null $invite_sent_at
 * @property string|null $invite_code
 * @property-read \App\Models\Organisation|null $organisation
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereInviteAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereInviteCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereInviteSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereUserId($value)
 */
	class PendingInvite extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property bool $enabled
 * @property string|null $description
 * @method static \Database\Factories\PermissionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $member_id
 * @property string $name
 * @method static \Database\Factories\ProfessionalRegistrationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereUpdatedAt($value)
 */
	class ProfessionalRegistration extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *      schema="Project",
 *      title="Project",
 *      description="Project model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example=1,
 *          description="Model primary key"
 *      ),
 *      @OA\Property(property="title",
 *          type="string",
 *          example="Project name"
 *      ),
 *      @OA\Property(property="unique_id",
 *          type="string",
 *          example="89AItHDuaqXsfgqOA85d"
 *      ),
 *      @OA\Property(property="lay_summary",
 *          type="string",
 *          example="This study aims to evaluate how digital mental health interventions (such as mobile apps for meditation, cognitive behavioral therapy, and mental health tracking) affect the mental health and well-being of young adults aged 18-30."
 *      ),
 *      @OA\Property(property="public_benefit",
 *          type="string",
 *          example="The findings from this research could lead to improved digital health interventions tailored to the mental health needs of young adults.",
 *          description="A unique identifier for Custodian's within SOURSD"
 *      ),
 *      @OA\Property(property="request_category_type",
 *          type="string",
 *          example="Health and Social Research"
 *      ),
 *      @OA\Property(property="technical_summary",
 *          type="string",
 *          example="This project involves analyzing anonymized, aggregated data from digital health applications used by young adults."
 *      ),
 *      @OA\Property(property="other_approval_commitees",
 *          type="string",
 *          example="This project requires approval from:  University Institutional Review Board (IRB) to ensure ethical considerations are met. Data Access Committee (DAC) from the app providers to secure permissions for using anonymized, aggregated data."
 *      ),
 *      @OA\Property(property="start_date",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="end_date",
 *          type="string",
 *          example="2024-10-10T15:03:00Z"
 *      )
 * )
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $unique_id
 * @property string $title
 * @property string|null $lay_summary
 * @property string|null $public_benefit
 * @property string|null $request_category_type
 * @property string|null $technical_summary
 * @property string|null $other_approval_committees
 * @property string|null $start_date
 * @property string|null $end_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Custodian> $approvals
 * @property-read int|null $approvals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Custodian> $custodians
 * @property-read int|null $custodians_count
 * @property-read \App\Models\ModelState|null $modelState
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organisation> $organisations
 * @property-read int|null $organisations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectHasUser> $projectUsers
 * @property-read int|null $project_users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project applySorting()
 * @method static \Database\Factories\ProjectFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project filterByCommon()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project filterByState()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereLaySummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereOtherApprovalCommittees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project wherePublicBenefit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereRequestCategoryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereTechnicalSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereUniqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project withoutTrashed()
 */
	class Project extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *      schema="ProjectDetail",
 *      title="Project Detail",
 *      description="ProjectDetail model",
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
 *      @OA\Property(property="project_id",
 *          type="integer",
 *          example=1,
 *          description="Primary key of associated Project for this ProjectDetail"
 *      ),
 *      @OA\Property(property="datasets",
 *          type="array",
 *          example="[
 *              'https://url.to.dataset/1',
 *              'https://url.to.dataset/2'
 *          ]",
 *          @OA\Items()
 *      ),
 *      @OA\Property(property="other_approval_committees",
 *          type="array",
 *          example="[
 *              'Name and description of approval committee'
 *          ]",
 *          @OA\Items()
 *      ),
 *      @OA\Property(property="data_sensitivity_level",
 *          type="string",
 *          example="Anonymous"
 *      ),
 *      @OA\Property(property="legal_basis_for_data_article6",
 *          type="string",
 *          example="Legal basis..."
 *      ),
 *      @OA\Property(property="duty_of_confidentiality",
 *          type="bool",
 *          example="true"
 *      ),
 *      @OA\Property(property="national_data_optout",
 *          type="bool",
 *          example="false"
 *      ),
 *      @OA\Property(property="request_frequency",
 *          type="string",
 *          enum={"ONE-OFF", "RECURRING"}
 *      ),
 *      @OA\Property(property="dataset_linkage_description",
 *          type="string",
 *          example="Datasets are linked by means of..."
 *      ),
 *      @OA\Property(property="data_minimisation",
 *          type="string",
 *          example="Approach to data minimisation is..."
 *      ),
 *      @OA\Property(property="data_use_description",
 *          type="string",
 *          example="Description of the data being used..."
 *      ),
 *      @OA\Property(property="access_date",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="access_type",
 *          type="integer",
 *          example=0
 *      ),
 *      @OA\Property(property="data_privacy",
 *          type="string",
 *          example="Our data privacy methods are..."
 *      ),
 *      @OA\Property(property="research_outputs",
 *          type="object",
 *          example={
 *               "https://yourdomain.com/research_output_1",
 *               "https://yourdomain.com/research_output_2"
 *          }
 *      ),
 *      @OA\Property(property="data_assets",
 *          type="string",
 *          example="Our data assets are..."
 *      )
 * )
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $project_id
 * @property string|null $datasets
 * @property string|null $other_approval_committees
 * @property string|null $data_sensitivity_level
 * @property string|null $legal_basis_for_data_article6
 * @property int $duty_of_confidentiality
 * @property int $national_data_optout
 * @property string|null $request_frequency
 * @property string|null $dataset_linkage_description
 * @property string|null $data_minimisation
 * @property string|null $data_use_description
 * @property string|null $access_date
 * @property string|null $access_type
 * @property string|null $data_privacy
 * @property string|null $research_outputs
 * @property string|null $data_assets
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereAccessDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereAccessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereDataAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereDataMinimisation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereDataPrivacy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereDataSensitivityLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereDataUseDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereDatasetLinkageDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereDatasets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereDutyOfConfidentiality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereLegalBasisForDataArticle6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereNationalDataOptout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereOtherApprovalCommittees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereRequestFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereResearchOutputs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectDetail whereUpdatedAt($value)
 */
	class ProjectDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $project_id
 * @property int $custodian_id
 * @property int $approved
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian whereProjectId($value)
 */
	class ProjectHasCustodian extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $project_id
 * @property int $organisation_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation whereProjectId($value)
 */
	class ProjectHasOrganisation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property-read \App\Models\ProjectRole|null $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasRole query()
 */
	class ProjectHasRole extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $project_id
 * @property string $user_digital_ident
 * @property int|null $project_role_id
 * @property int $primary_contact
 * @property int|null $affiliation_id
 * @property-read \App\Models\Affiliation|null $affiliation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectHasCustodian> $approvals
 * @property-read int|null $approvals_count
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\Registry|null $registry
 * @property-read \App\Models\ProjectRole|null $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser whereAffiliationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser wherePrimaryContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser whereProjectRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser whereUserDigitalIdent($value)
 */
	class ProjectHasUser extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
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
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectRole whereUpdatedAt($value)
 */
	class ProjectRole extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $project_id
 * @property int $user_id
 * @property int $custodian_id
 * @property int $approved
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Custodian $custodian
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereUserId($value)
 */
	class ProjectUserCustodianApproval extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *      schema="Registry",
 *      title="Registry",
 *      description="Registry model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example="1"
 *      ),
 *      @OA\Property(property="created_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(property="updated_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(property="deleted_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(property="digi_ident",
 *          type="string",
 *          example="$2y$12$Ssrz04d0bfw2X9t3juq9K.WPUgPNplXr1FHbdjoTeLajgVGGRxiqG"
 *      ),
 *      @OA\Property(property="dl_ident",
 *          type="string",
 *          example=""
 *      ),
 *      @OA\Property(property="pp_ident",
 *          type="string",
 *          example=""
 *      ),
 *      @OA\Property(property="verified",
 *          type="integer",
 *          example="1"
 *      ),
 * )
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $dl_ident
 * @property string|null $pp_ident
 * @property string $digi_ident
 * @property bool $verified
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accreditation> $accreditations
 * @property-read int|null $accreditations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Affiliation> $affiliations
 * @property-read int|null $affiliations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Education> $education
 * @property-read int|null $education_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\History> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\Identity|null $identity
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Training> $professionalRegistrations
 * @property-read int|null $professional_registrations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectHasUser> $projectUsers
 * @property-read int|null $project_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Training> $trainings
 * @property-read int|null $trainings_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\RegistryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereDigiIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereDlIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry wherePpIdent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry whereVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registry withoutTrashed()
 */
	class Registry extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $registry_id
 * @property int $accreditation_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation whereAccreditationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation whereRegistryId($value)
 */
	class RegistryHasAccreditation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $affiliation_id
 * @property int $registry_id
 * @property-read \App\Models\Affiliation|null $affiliation
 * @property-read \App\Models\ModelState|null $modelState
 * @property-read \App\Models\Registry|null $registry
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAffiliation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAffiliation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAffiliation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAffiliation whereAffiliationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAffiliation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAffiliation whereRegistryId($value)
 */
	class RegistryHasAffiliation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $registry_id
 * @property int $education_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation whereEducationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation whereRegistryId($value)
 */
	class RegistryHasEducation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $registry_id
 * @property int $employment_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment whereEmploymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment whereRegistryId($value)
 */
	class RegistryHasEmployment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $registry_id
 * @property string $file_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasFile whereRegistryId($value)
 */
	class RegistryHasFile extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $registry_id
 * @property int $history_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory whereHistoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory whereRegistryId($value)
 */
	class RegistryHasHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $professional_registration_id
 * @property int $registry_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration whereProfessionalRegistrationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration whereRegistryId($value)
 */
	class RegistryHasProfessionalRegistration extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $registry_id
 * @property int $training_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasTraining newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasTraining newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasTraining query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasTraining whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasTraining whereTrainingId($value)
 */
	class RegistryHasTraining extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $custodian_id
 * @property int $registry_id
 * @property int $status
 * @property string|null $approved_at
 * @property string|null $rejected_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereUpdatedAt($value)
 */
	class RegistryReadRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $comment
 * @property int $custodian_by
 * @property int $registry_id
 * @property bool $resolved
 * @method static \Database\Factories\ResolutionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereCustodianBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereUpdatedAt($value)
 */
	class Resolution extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Custodian> $custodians
 * @property-read int|null $custodians_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules whereTitle($value)
 */
	class Rules extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector withoutTrashed()
 */
	class Sector extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereUpdatedAt($value)
 */
	class State extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $town
 * @property string|null $county
 * @property string|null $country
 * @property string|null $postcode
 * @method static \Database\Factories\SubsidiaryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereTown($value)
 */
	class Subsidiary extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $value
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereValue($value)
 */
	class SystemConfig extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *      schema="Training",
 *      title="Training",
 *      description="Training model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example="123"
 *      ),
 *      @OA\Property(property="created_at",
 *          type="string",
 *          example="2024-02-04 12:00:00"
 *      ),
 *      @OA\Property(property="updated_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(property="registry_id",
 *          type="integer",
 *          example="1"
 *      ),
 *      @OA\Property(property="provider",
 *          type="string",
 *          example="ONS"
 *      ),
 *      @OA\Property(property="awarded_at",
 *          type="string",
 *          example="2024-02-04 12:10:00"
 *      ),
 *      @OA\Property(property="expires_at",
 *          type="string",
 *          example="2026-02-04 12:09:59"
 *      ),
 *      @OA\Property(property="expires_in_years",
 *          type="integer",
 *          example="2"
 *      ),
 *      @OA\Property(property="training_name",
 *          type="string",
 *          example="Safe Researcher Training"
 *      ),
 *      @OA\Property(property="pro_registration",
 *          type="integer",
 *          example="1"
 *      )
 * )
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $provider
 * @property string $awarded_at
 * @property string $expires_at
 * @property int $expires_in_years
 * @property string $training_name
 * @property int|null $certification_id
 * @property int $pro_registration
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training applySorting()
 * @method static \Database\Factories\TrainingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereAwardedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereCertificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereExpiresInYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereProRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereTrainingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Training whereUpdatedAt($value)
 */
	class Training extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $training_id
 * @property int $file_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingHasFile whereTrainingId($value)
 */
	class TrainingHasFile extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $first_name
 * @property string $last_name
 * @property string $organisation_name
 * @property string $accreditation_number
 * @property string $accreditation_type
 * @property string $expiry_date
 * @property string $public_record
 * @property string $stage
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereAccreditationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereAccreditationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereOrganisationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed wherePublicRecord($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereUpdatedAt($value)
 */
	class UksaLiveFeed extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Components (
 * @OA\Schema (
 *      schema="User",
 *      title="User",
 *      description="User model",
 *      @OA\Property(
 *          property="id",
 *          type="integer",
 *          example="123"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          type="string",
 *          example="2024-02-04 12:00:00"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(
 *          property="first_name",
 *          type="string",
 *          example="A"
 *      ),
 *      @OA\Property(
 *          property="last_name",
 *          type="string",
 *          example="Researcher"
 *      ),
 *      @OA\Property(
 *          property="email",
 *          type="string",
 *          example="person@somewhere.com"
 *      ),
 *      @OA\Property(
 *          property="email_verified_at",
 *          type="string",
 *          example="2024-02-04 12:00:00"
 *      ),
 *      @OA\Property(
 *          property="consent_scrape",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(
 *          property="public_opt_in",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(
 *          property="declaration_signed",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(
 *          property="organisation_id",
 *          type="integer",
 *          example="123"
 *      ),
 *      @OA\Property(
 *          property="orcid_scanning",
 *          type="integer",
 *          example="1"
 *      ),
 *      @OA\Property(
 *          property="orcid_scanning_completed_at",
 *          type="string",
 *          example="2024-02-04 12:01:00"
 *      ),
 *      @OA\Property(
 *          property="location",
 *          type="string",
 *          example="United Kingdom"
 *      ),
 *      @OA\Property(
 *          property="t_and_c_agreed",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(
 *          property="t_and_c_agreement_date",
 *          type="string",
 *          example="2024-02-04 12:00:00"
 *     )
 * )
 * )
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $name
 * @property string $email
 * @property string|null $password
 * @property int|null $registry_id
 * @property string|null $provider
 * @property string|null $keycloak_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $otp
 * @property string $user_group
 * @property bool $consent_scrape
 * @property string|null $orc_id
 * @property int $unclaimed
 * @property string|null $feed_source
 * @property int $public_opt_in
 * @property bool $declaration_signed
 * @property int|null $organisation_id
 * @property bool $orcid_scanning
 * @property string|null $orcid_scanning_completed_at
 * @property int $is_delegate
 * @property int $is_org_admin
 * @property int|null $custodian_id
 * @property int|null $custodian_user_id
 * @property string|null $role
 * @property string|null $location
 * @property int $t_and_c_agreed
 * @property string|null $t_and_c_agreement_date
 * @property bool $uksa_registered
 * @property bool $is_sro
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionLog> $actionLogs
 * @property-read int|null $action_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Custodian> $approvals
 * @property-read int|null $approvals_count
 * @property-read \App\Models\Custodian|null $custodian
 * @property-read \App\Models\CustodianUser|null $custodian_user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Department> $departments
 * @property-read int|null $departments_count
 * @property-read mixed $evaluation
 * @property-read \App\Models\ModelState|null $modelState
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Organisation|null $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PendingInvite> $pendingInvites
 * @property-read int|null $pending_invites_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectHasUser> $projectUsers
 * @property-read int|null $project_users_count
 * @property-read \App\Models\Registry|null $registry
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionLog> $secondaryActionLogs
 * @property-read int|null $secondary_action_logs_count
 * @property-read mixed $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionLog> $tertiaryActionLogs
 * @property-read int|null $tertiary_action_logs_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User applySorting()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User filterByState()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User fromProject($projectId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereConsentScrape($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCustodianUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeclarationSigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFeedSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsDelegate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsOrgAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsSro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKeycloakId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrcId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrcidScanning($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrcidScanningCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePublicOptIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTAndCAgreed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTAndCAgreementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUksaRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUnclaimed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withProjectMembership($projectId)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string $api_name
 * @property string $api_details
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserApiToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserApiToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserApiToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserApiToken whereApiDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserApiToken whereApiName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserApiToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserApiToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserApiToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserApiToken whereUserId($value)
 */
	class UserApiToken extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $user_id
 * @property int $custodian_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval whereUserId($value)
 */
	class UserHasCustodianApproval extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $user_id
 * @property int $permission_id
 * @property int $custodian_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission whereUserId($value)
 */
	class UserHasCustodianPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $user_id
 * @property int $department_id
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments whereUserId($value)
 */
	class UserHasDepartments extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *     schema="ValidationCheck",
 *     type="object",
 *     title="Validation Check",
 *     required={"name", "description", "applies_to"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Check format"),
 *     @OA\Property(property="description", type="string", example="Ensures proper formatting of input"),
 *     @OA\Property(property="applies_to", type="string", example="user"),
 *     @OA\Property(property="enabled", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 * )
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \App\Enums\ValidationCheckAppliesTo $applies_to
 * @property int $enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Custodian> $custodians
 * @property-read int|null $custodians_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck applySorting()
 * @method static \Database\Factories\ValidationCheckFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck forContext(\App\Enums\ValidationCheckAppliesTo $context)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereAppliesTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereUpdatedAt($value)
 */
	class ValidationCheck extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *     schema="ValidationLog",
 *     title="Validation Log",
 *     description="Validation Log model",
 * 
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Model primary key"
 *     ),
 * 
 *     @OA\Property(
 *         property="entity_type",
 *         type="string",
 *         example="App\\Models\\Custodian",
 *         description="Type of the primary entity associated with the validation log"
 *     ),
 * 
 *     @OA\Property(
 *         property="entity_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the primary entity associated with the validation log"
 *     ),
 * 
 *     @OA\Property(
 *         property="secondary_entity_type",
 *         type="string",
 *         example="App\\Models\\Project",
 *         description="Type of the secondary entity associated with the validation log"
 *     ),
 * 
 *     @OA\Property(
 *         property="secondary_entity_id",
 *         type="integer",
 *         example=2,
 *         description="ID of the secondary entity associated with the validation log"
 *     ),
 * 
 *     @OA\Property(
 *         property="tertiary_entity_type",
 *         type="string",
 *         example="App\\Models\\Registry",
 *         description="Type of the tertiary entity associated with the validation log"
 *     ),
 * 
 *     @OA\Property(
 *         property="tertiary_entity_id",
 *         type="integer",
 *         example=3,
 *         description="ID of the tertiary entity associated with the validation log"
 *     ),
 * 
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Validation Check 1",
 *         description="Name of the validation log entry"
 *     ),
 * 
 *     @OA\Property(
 *         property="completed_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-10T15:43:00Z",
 *         description="Timestamp when the validation was completed (nullable)"
 *     ),
 * 
 *     @OA\Property(
 *         property="manually_confirmed",
 *         type="boolean",
 *         example=true,
 *         description="Whether the validation was manually confirmed"
 *     ),
 * )
 * @property int $id
 * @property string $entity_type
 * @property int|null $validation_check_id
 * @property int $entity_id
 * @property string|null $secondary_entity_type
 * @property int|null $secondary_entity_id
 * @property string|null $tertiary_entity_type
 * @property int|null $tertiary_entity_id
 * @property string|null $completed_at
 * @property int $manually_confirmed
 * @property int $enabled
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ValidationLogComment> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $entity
 * @property-read \App\Models\ValidationCheck|null $validationCheck
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereManuallyConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereSecondaryEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereSecondaryEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereTertiaryEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereTertiaryEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog whereValidationCheckId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLog withDisabled()
 */
	class ValidationLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @OA\Schema (
 *     schema="ValidationLogComment",
 *     title="Validation Log Comment",
 *     description="Comments on validation logs",
 * 
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Model primary key"
 *     ),
 * 
 *     @OA\Property(
 *         property="validation_log_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the associated validation log"
 *     ),
 * 
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the user who made the comment"
 *     ),
 * 
 *     @OA\Property(
 *         property="comment",
 *         type="string",
 *         example="This validation needs further review.",
 *         description="The comment text"
 *     ),
 * 
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-10T15:43:00Z",
 *         description="Timestamp when the comment was created"
 *     ),
 * 
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-10T16:00:00Z",
 *         description="Timestamp when the comment was last updated"
 *     ),
 * )
 * @property int $id
 * @property int $validation_log_id
 * @property int $user_id
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\ValidationLog $validationLog
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLogComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLogComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLogComment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLogComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLogComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLogComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLogComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLogComment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationLogComment whereValidationLogId($value)
 */
	class ValidationLogComment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $description
 * @property int $enabled
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustodianWebhookReceiver> $receivers
 * @property-read int|null $receivers_count
 * @method static \Database\Factories\WebhookEventTriggerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereUpdatedAt($value)
 */
	class WebhookEventTrigger extends \Eloquent {}
}

