<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Custodian>
     */
    public function custodians(): BelongsToMany
    {
        return $this->belongsToMany(Custodian::class, 'custodian_has_rules', 'rule_id', 'custodian_id');
    }
}
