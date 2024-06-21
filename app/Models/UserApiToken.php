<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
