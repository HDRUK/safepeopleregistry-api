<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string $from
 * @property string $to
 * @property string $institute_name
 * @property string|null $institute_address
 * @property string|null $institute_identifier
 * @property string|null $source
 * @property int $registry_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereInstituteAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereInstituteIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereInstituteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Education whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Education extends Model
{
    use HasFactory;
    use SearchManager;

    public $table = 'educations';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'from',
        'to',
        'institute_name',
        'institute_address',
        'institute_identifier',
        'source',
        'registry_id',
    ];

    protected static array $searchableColumns = [
        'title',
        'institute_name',
    ];

    protected static array $sortableColumns = [
        'title',
        'institute_name',
    ];
}
