<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rules extends Model
{
    use HasFactory;

    protected $table = 'rules';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'title',
        'description'
    ];

    protected $hidden = ['pivot'];

    public function custodians()
    {
        return $this->belongsToMany(Custodian::class, 'custodian_has_rules', 'rule_id', 'custodian_id');
    }
}
