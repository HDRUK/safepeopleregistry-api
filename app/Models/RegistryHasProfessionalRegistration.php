<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistryHasProfessionalRegistration extends Model
{
    use HasFactory;

    public $table = 'registry_has_professional_registrations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'professional_registration_id',
    ];
}
