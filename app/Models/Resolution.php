<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resolution extends Model
{
    use HasFactory;

    public $table = 'resolutions';

    public $timestamps = true;

    protected $fillable = [
        'comment',
        'custodian_by',
        'registry_id',
        'resolved',
    ];

    protected $casts = [
        'resolved' => 'boolean',
    ];
}
