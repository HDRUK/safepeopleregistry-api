<?php

namespace App\Models;

use App\Observers\FileObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    public $timestamps = true;

    public const FILE_STATUS_PENDING = 'pending';
    public const FILE_STATUS_PROCESSED = 'processed';
    public const FILE_STATUS_FAILED = 'failed';

    public const FILE_TYPE_RESEARCHER_LIST = 'researcher_list';
    public const FILE_TYPE_CV = 'cv';
    public const FILE_TYPE_TRAINING_EVIDENCE = 'training_evidence';

    protected $fillable = [
        'name',
        'type',
        'path',
        'status',
    ];
}
