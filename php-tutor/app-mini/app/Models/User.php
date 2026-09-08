<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 用户模型,密码使用 password_hash 存储且永不出现在序列化结果中。
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $password
 */
class User extends Model
{
    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password'];

    protected $casts = [
        'id' => 'int',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }
}
