<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasIssuerPermission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'user_has_issuer_permissions';

    /**
     * Whether or not this model supports timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * What fields of this model are accepted as parameters
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'permission_id',
        'issuer_id',
    ];
}
