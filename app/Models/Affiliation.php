<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    use HasFactory;

    public $table = 'affiliations';

    public $timestamps = true;

    protected $fillable = [
        'organisation_id',
        'current_employer',
        'member_id',
        'relationship'
    ];

    /**
     * Get the permissions associated with the custodian user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'id');
    }
}
