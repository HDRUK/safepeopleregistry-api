<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationHasSubsidiary extends Model
{
    use HasFactory;

    public $table = 'organisation_has_subsidiaries';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'subsidiary_id',
    ];
}
