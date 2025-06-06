<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
