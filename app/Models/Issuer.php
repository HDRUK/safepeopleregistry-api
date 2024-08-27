<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issuer extends Model
{
    use HasFactory;

    protected $table = 'issuers';

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
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'idvt_required' => 'boolean',
    ];

    protected $hidden = [
        'unique_identifier',
        'calculated_hash',
    ];
}
