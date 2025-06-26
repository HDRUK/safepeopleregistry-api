<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *     schema="OrganisationDelegate",
 *     type="object",
 *     title="OrganisationDelegate",
 *     description="Model representing organisation delegates",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the organisation delegate"
 *     ),
 *     @OA\Property(
 *         property="first_name",
 *         type="string",
 *         example="John",
 *         description="First name of the delegate"
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         type="string",
 *         example="Doe",
 *         description="Last name of the delegate"
 *     ),
 *     @OA\Property(
 *         property="is_dpo",
 *         type="integer",
 *         example=1,
 *         description="Indicates if the delegate is a Data Protection Officer (1 for yes, 0 for no)"
 *     ),
 *     @OA\Property(
 *         property="is_hr",
 *         type="integer",
 *         example=0,
 *         description="Indicates if the delegate is part of HR (1 for yes, 0 for no)"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         example="john.doe@example.com",
 *         description="Email address of the delegate"
 *     ),
 *     @OA\Property(
 *         property="priority_order",
 *         type="integer",
 *         example=1,
 *         description="Priority order of the delegate"
 *     ),
 *     @OA\Property(
 *         property="organisation_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the organisation associated with the delegate"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the delegate record was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the delegate record was last updated"
 *     )
 * )
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
 * @mixin \Eloquent
 */
class OrganisationDelegate extends Model
{
    use HasFactory;

    public $table = 'organisation_delegates';

    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'is_dpo',
        'is_hr',
        'email',
        'priority_order',
        'organisation_id',
    ];

    protected $hidden = [
        'email',
        'is_dpo',
        'is_hr',
    ];
}
