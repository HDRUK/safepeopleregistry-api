<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

class Department extends Model
{
    use HasFactory;
    use SearchManager;

    public $table = 'departments';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'organisation_id',
    ];

    protected static array $searchableColumns = [
        'name',
    ];

    protected static array $sortableColumns = [
        'name',
    ];
}
