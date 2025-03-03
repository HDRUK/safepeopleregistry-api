<?php

namespace App\Models;

use App\Observers\CustodianObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * @OA\Schema(
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
 *      )
 * )
 */
#[ObservedBy([CustodianObserver::class])]
class Custodian extends Model
{
    use HasFactory;
    use SearchManager;

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
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'idvt_required' => 'boolean',
    ];

    protected $hidden = [
        'calculated_hash',
    ];

    protected static array $searchableColumns = [
        'name',
        'contact_email',
    ];

    protected static array $sortableColumns = [
        'name',
        'contact_email',
    ];

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rules::class, 'custodian_has_rules', 'custodian_id', 'rule_id');
    }
}
