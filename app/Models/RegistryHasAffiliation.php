<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Traits\StateWorkflow;

class RegistryHasAffiliation extends Model
{
    use HasFactory;
    use StateWorkflow;

    public $table = 'registry_has_affiliations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'affiliation_id',
    ];

    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Affiliation>
    */
    public function affiliation(): BelongsTo
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id');
    }

    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Registry>
    */
    public function registry(): BelongsTo
    {
        return $this->belongsTo(Registry::class, 'registry_id');
    }

    public function modelState(): MorphOne
    {
        return $this->morphOne(ModelState::class, 'stateable');
    }
}
