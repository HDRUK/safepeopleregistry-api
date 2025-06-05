<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="ValidationLogComment",
 *     title="Validation Log Comment",
 *     description="Comments on validation logs",
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Model primary key"
 *     ),
 *
 *     @OA\Property(
 *         property="validation_log_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the associated validation log"
 *     ),
 *
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the user who made the comment"
 *     ),
 *
 *     @OA\Property(
 *         property="comment",
 *         type="string",
 *         example="This validation needs further review.",
 *         description="The comment text"
 *     ),
 *
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-10T15:43:00Z",
 *         description="Timestamp when the comment was created"
 *     ),
 *
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-10T16:00:00Z",
 *         description="Timestamp when the comment was last updated"
 *     ),
 * )
 */
class ValidationLogComment extends Model
{
    use HasFactory;

    protected $table = 'validation_log_comments';

    protected $fillable = [
        'validation_log_id',
        'user_id',
        'comment',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ValidationLog>
     */
    public function validationLog(): BelongsTo
    {
        return $this->belongsTo(ValidationLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
