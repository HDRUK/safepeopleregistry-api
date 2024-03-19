<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'histories';

    /**
     * Whether or not this model supports timestamps
     * 
     * @var bool
     */
    public $timestamps = true;

    /**
     * What fields of this model are accepted as parameters
     * 
     * @var array
     */
    protected $fillable = [
        'employment_id',
        'endorsement_id',
        'infringement_id',
        'project_id',
        'access_key_id',
        'issuer_identifier',
        'ledger_hash',
    ];
}
