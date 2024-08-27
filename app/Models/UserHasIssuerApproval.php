<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasIssuerApproval extends Model
{
    use HasFactory;

    protected $table = 'user_has_issuer_approvals';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'issuer_id',
    ];
}
