<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema(
 *    schema="Tag",
 *        @OA\Property(
 *            property="id",
 *            description="Task identifier",
 *            type="integer",
 *            nullable="false",
 *            example="1"
 *        ),
 *        @OA\Property(
 *            property="name",
 *            description="tag name",
 *            type="string",
 *            nullable="false",
 *            example="Important"
 *        ),
 *    )
 * )
 */
class Tag extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The roles that belong to the user.
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }
}
