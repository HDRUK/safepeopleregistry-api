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
 * @mixin \Eloquent
 */
class UserApiToken extends Model
{
    use HasFactory;

    public $table = 'user_api_tokens';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'api_name',
        'api_details',
    ];
}
