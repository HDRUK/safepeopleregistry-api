<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Custodian extends Model
{
    use HasFactory;
    use SearchManager;

    protected $table = 'custodians';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'unique_identifier',
        'calculated_hash',
        'contact_email',
        'enabled',
        'invite_accepted_at',
        'invite_sent_at',
        'idvt_required',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'idvt_required' => 'boolean',
    ];

    protected $hidden = [
        'calculated_hash',
    ];

    protected static array $searchableColumns = [
        'name',
        'contact_email',
    ];

    protected static array $sortableColumns = [
        'name',
        'contact_email',
    ];

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rules::class, 'custodian_has_rules', 'custodian_id', 'rule_id');
    }
}
