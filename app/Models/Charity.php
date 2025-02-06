<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charity extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'name',
        'website',
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
    ];

    public $timestamps = false;

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'organisation_has_charity');
    }
}
