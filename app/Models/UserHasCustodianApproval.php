<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasCustodianApproval extends Model
{
    use HasFactory;

    protected $table = 'user_has_custodian_approvals';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'custodian_id',
    ];
}
