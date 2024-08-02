<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'projects';

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
        'unique_id',
        'title',
        'lay_summary',
        'public_benefit',
        'request_category_type',
        'technical_summary',
        'other_approval_committes',
        'start_date',
        'end_date',
        'affiliate_id',
    ];
}
