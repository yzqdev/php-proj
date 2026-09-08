<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models\Modules\Users\Models{
/**
 * App\Models\Modules\Users\Models\CatchController
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CatchController newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CatchController newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CatchController query()
 */
	class CatchController extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $username 昵称
 * @property string $password 密码
 * @property string $email 邮箱
 * @property string|null $avatar 头像
 * @property string|null $remember_token token
 * @property int $department_id 部门ID
 * @property int $creator_id
 * @property int $status 1:normal 2: forbidden
 * @property string|null $login_ip 登录IP
 * @property int $login_at 登录时间
 * @property \Illuminate\Support\Carbon $created_at created time
 * @property \Illuminate\Support\Carbon $updated_at updated time
 * @property int $deleted_at delete time
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 */
	class User extends \Eloquent {}
}

