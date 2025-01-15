<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subsidiary extends Model
{
    use HasFactory;

    protected $table = 'subsidiaries';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
    ];

}
