<?php

namespace App\Models;

use Auth;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *    schema="Task",
 *        @OA\Property(
 *            property="id",
 *            description="Task identifier",
 *            type="integer",
 *            nullable="false",
 *            example="1"
 *        ),
 *        @OA\Property(
 *            property="title",
 *            description="Title of the task",
 *            type="string",
 *            nullable="false",
 *            example="Create Swagger Documentation"
 *        ),
 *        @OA\Property(
 *            property="description",
 *            description="Destription of the Task",
 *            type="string",
 *            nullable="true",
 *            example="Lorem ipsum dolor"
 *        ),
 *        @OA\Property(
 *            property="due_date",
 *            description="Due date of the Task with timezone",
 *            type="string",
 *            nullable="true",
 *            example="2004-10-19 16:23:54.000 +0800"
 *        ),
 *        @OA\Property(
 *            property="status",
 *            description="Status of the Task if it is completed",
 *            type="integer",
 *            nullable="true",
 *            example=1
 *        ),
 *        @OA\Property(
 *            property="priority",
 *            description="Priority of the Task",
 *            type="integer",
 *            nullable="true",
 *            example="1"
 *        ),
 *        @OA\Property(
 *            property="is_archived",
 *            description="Flag to archive a task",
 *            type="boolean",
 *            nullable="true",
 *            example=true
 *        ),
 *        @OA\Property(
 *            property="order",
 *            description="Sort order of a task",
 *            type="integer",
 *            nullable="true",
 *            example=1
 *        ),
 *        @OA\Property(
 *            property="tags",
 *            description="Tags of the task",
 *            type="array",
 *            nullable="true",
 *            @OA\Items(
 *              ref="#/components/schemas/Tag"
 *            )
 *        ),
 *    )
 * )
 */
class Task extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'due_date', 'status', 'priority', 'is_archived', 'order'];
    protected $sortFields = ['id', 'title', 'description', 'due_date', 'created_at', 'status', 'priority', 'is_archived', 'order'];
    public const DEFAULT_SORT_FIELD = 'id';
    public const STATUS_PENDING = 1;

    public static function create(Request $request)
    {

        $task = new Task($request->all());
        $user = Auth::user();

        $priority = $request->priority;
        if (null === $priority) {
            $task->priority = 4;
        }

        $task->setOrder($user->tasks()->count() + 1);
        $user->tasks()->save($task);
        return $task;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSortFields(): array
    {
        return $this->sortFields;
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * The roles that belong to the user.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function setOrder(int $order)
    {
        $this->order = $order;
    }

}
