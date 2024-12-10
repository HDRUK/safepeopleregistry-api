<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\SearchManager;

class Sector extends Model
{
    use SoftDeletes;
    use SearchManager;

    protected $table = 'sectors';

    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    public const SECTORS = [
        'NHS',
        'Academia',
        'NGO',
        'Public',
        'Charity/Non-profit',
        'Private/Industry',
    ];

    protected static array $searchableColumns = [
        'name',
    ];

    protected static array $sortableColumns = [
        'name',
    ];
}
