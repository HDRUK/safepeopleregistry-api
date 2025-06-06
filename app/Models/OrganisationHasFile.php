<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $organisation_id
 * @property int $file_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasFile whereOrganisationId($value)
 * @mixin \Eloquent
 */
class OrganisationHasFile extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_files';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'file_id',
    ];
}
