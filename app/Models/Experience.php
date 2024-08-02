<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    /**
     * The table associated with this model
     *
     * @var string
     */
    protected $table = 'experiences';

    /**]
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
        'project_id',
        'from',
        'to',
        'organisation_id',
    ];
}
