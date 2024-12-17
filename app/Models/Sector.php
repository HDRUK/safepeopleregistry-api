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
        'NGO',
        'Public',
        'Healthcare Providers',
        'Pharmaceutical and Biotechnology Companies (non-SME)',
        'Pharmaceutical and Biotechnology Companies (SME)',
        'Academic Research Institutions',
        'Government Agencies: e.g., DHSC, Regulatory bodies, NICE',
        'Non-Profit Organisations (e.g., foundations, advocacy groups, charities)',
        'Other for-profit organisations (non-SME)',
        'Other for-profit organisations (SME)',
    ];

    protected static array $searchableColumns = [
        'name',
    ];

    protected static array $sortableColumns = [
        'name',
    ];
}
