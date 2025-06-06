<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $registry_id
 * @property int $accreditation_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation whereAccreditationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation whereRegistryId($value)
 * @mixin \Eloquent
 */
class RegistryHasAccreditation extends Model
{
    use HasFactory;

    public $table = 'registry_has_accreditations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'accreditation_id',
    ];
}
