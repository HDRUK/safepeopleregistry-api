<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UksaLiveFeed extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $table = 'uksa_live_feeds';

    protected $fillable = [
        'first_name',
        'last_name',
        'organisation_name',
        'accreditation_number',
        'accreditation_type',
        'expiry_date',
        'public_record',
        'stage',
    ];
}
