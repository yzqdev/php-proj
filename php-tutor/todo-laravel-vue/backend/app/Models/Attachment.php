<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * "id": 7,
            "filename": "cover-letter-pd-1694024704.pdf",
            "type": "pdf",
            "path": "attachments\/cover-letter-pd-1694024704.pdf",
            "task_id": 5,
            "created_at": "2023-09-06T18:25:04.000000Z",
            "updated_at": "2023-09-06T18:25:04.000000Z"
 */

/**
 * @OA\Schema(
 *    schema="Attachment",
 *        @OA\Property(
 *            property="id",
 *            description="Attachment identifier",
 *            type="integer",
 *            nullable="false",
 *            example="1"
 *        ),
 *        @OA\Property(
 *            property="task_id",
 *            description="Task identifier",
 *            type="integer",
 *            nullable="false",
 *            example="1"
 *        ),
 *        @OA\Property(
 *            property="filename",
 *            description="Attachments filename",
 *            type="string",
 *            nullable="true",
 *            example="test-attachment.docx"
 *        ),
 *       @OA\Property(
 *            property="created_at",
 *            description="date created",
 *            type="date",
 *            nullable="false",
 *            example="2023-09-06T18:25:04.000000Z"
 *        ),
 *       @OA\Property(
 *            property="updated_at",
 *            description="date updated",
 *            type="date",
 *            nullable="false",
 *            example="2023-09-06T18:25:04.000000Z"
 *        ),
 *        @OA\Property(
 *            property="path",
 *            description="Path of the upoaded file",
 *            type="string",
 *            nullable="true",
 *            example="attachments\/cover-letter-pd-1694024704.pdf"
 *        ),
 *        @OA\Property(
 *            property="type",
 *            description="file type extension of the attachment",
 *            type="string",
 *            nullable="false",
 *            example="pdf"
 *        ),
 *    )
 * )
 */
class Attachment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }


}
