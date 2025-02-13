<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalRegistration extends Model
{
    use HasFactory;

    public $table = 'professional_registrations';

    public $timestamps = true;

    protected $fillable = [
        'member_id',
        'name',
    ];
}
