<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EntityModel extends Model
{
    use HasFactory;
    use SearchManager;

    protected $table = 'entity_models';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'entity_model_type_id',
        'calls_file',
        'file_path',
        'calls_operation',
        'operation',
        'active',
    ];

    protected $casts = [
        'calls_file' => 'boolean',
        'calls_operation' => 'boolean',
    ];

    protected $hidden = [
        'file_path',
        'operation',
    ];

    protected static array $searchableColumns = [
        'name',
    ];

    protected static array $sortableColumns = [
        'name',
    ];

    public function custodianModelConfig(): HasOne
    {
        return $this->hasOne(CustodianModelConfig::class);
    }
}
