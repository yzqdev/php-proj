<?php

/**
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Models;

use App\Models\Thread;
use App\Events\Category\Created;
use Carbon\Carbon;
use Discuz\Base\DzqModel;
use Discuz\Database\ScopeVisibilityTrait;
use Discuz\Foundation\EventGeneratorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $icon
 * @property int $sort
 * @property int $property
 * @property int $thread_count
 * @property array $moderators
 * @property string $ip
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $parentid
 */
class Category extends DzqModel
{
    use EventGeneratorTrait;
    use ScopeVisibilityTrait;

    /**
     * {@inheritdoc}
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * 分类权限
     *
     * @var array
     */
    public static $categoryPermissions = [
        'viewThreads',                 // 查看帖子列表
        'createThread',                // 发布帖子
        'thread.reply',                // 回复帖子
        'thread.canBeReward',          // 是否允许被打赏
        'thread.viewPosts',            // 查看详情
        'thread.edit',                 // 编辑帖子
        'thread.hide',                 // 删除帖子
        'thread.essence',              // 加精帖子
        'thread.editPosts',            // 编辑回复
        'thread.hidePosts',            // 删除回复
        'thread.editOwnThreadOrPost',  // 编辑自己主题或回复的权限
        'thread.hideOwnThreadOrPost',  // 删除自己主题或回复的权限
        'thread.editOriginalThreadOrPost',  // 编辑自己主题原创的权限
        'thread.freeViewPosts.1',      // 免费查看付费帖子
        'thread.freeViewPosts.2',      // 免费查看付费视频
        'thread.freeViewPosts.3',      // 免费查看付费图片
        'thread.freeViewPosts.4',      // 免费查看付费语音
        'thread.freeViewPosts.5',      // 免费查看付费问答
        'viewaskThreads',                 // 查看提问帖子列表
        'createaskThread',                // 发布提问帖子
        'thread.ask',                // 发布提问
        'thread.canaskBeReward',          // 是否允许被打赏
        'thread.askreply',                // 回复提问帖子
        'thread.viewaskPosts',            // 查看提问详情
        'thread.askedit',                 // 提问编辑帖子
        'thread.askhide',                 // 提问删除帖子
        'thread.askessence',              // 提问加精帖子
        'thread.editaskPosts',            // 提问编辑回复
        'thread.hideaskPosts',            // 提问删除回复
        'thread.editaskOwnThreadOrPost',  // 提问编辑自己主题或回复的权限
        'thread.hideaskOwnThreadOrPost',  // 提问删除自己主题或回复的权限
        'thread.freeaskViewPosts.1',      // 免费查看提问付费帖子
        'thread.freeaskViewPosts.2',      // 免费查看提问付费视频
        'thread.freeaskViewPosts.3',      // 免费查看提问付费图片
        'thread.freeaskViewPosts.4',      // 免费查看提问付费语音
        'thread.freeaskViewPosts.5',      // 免费查看提问付费问答
        'viewkechengThreads',                 // 查看课程帖子列表
        'createkechengThread',                // 发布课程帖子
        'thread.kecheng',                // 发布课程
        'thread.cankechengBeReward',          // 是否允许课程被打赏
        'thread.kechengreply',                // 回复课程帖子
        'thread.viewkechengPosts',            // 查看课程详情
        'thread.kechengedit',                 // 课程编辑帖子
        'thread.kechenghide',                 // 课程删除帖子
        'thread.kechengessence',              // 课程加精帖子
        'thread.editkechengPosts',            // 课程编辑回复
        'thread.hidekechengPosts',            // 课程删除回复
        'thread.editkechengOwnThreadOrPost',  // 课程编辑自己主题或回复的权限
        'thread.hidekechengOwnThreadOrPost',  // 课程删除自己主题或回复的权限
        'thread.freekechengViewPosts.1',      // 免费查看课程付费帖子
        'thread.freekechengViewPosts.2',      // 免费查看课程付费视频
        'thread.freekechengViewPosts.3',      // 免费查看课程付费图片
        'thread.freekechengViewPosts.4',      // 免费查看课程付费语音
        'thread.freekechengViewPosts.5',      // 免费查看课程付费问答
    ];

    /**
     * Create a new category.
     *
     * @param string $name
     * @param string $description
     * @param int $sort
     * @param string $icon
     * @param string $ip
     * @return static
     */
    public static function build(string $name, string $description, string $show, string $url, int $sort, int $parentid, int $type, string $icon = '', string $ip = '')
    {
        $category = new static;

        $category->name = $name;
        $category->description = $description;
        $category->show = $show;
        $category->url = $url;
        $category->sort = $sort;
        $category->parentid = $parentid;
        $category->type = $type;
        $category->icon = $icon;
        $category->ip = $ip;

        $category->raise(new Created($category));

        return $category;
    }

    /**
     * @param string $value
     * @return array
     */
    public function getModeratorsAttribute($value)
    {
        return explode(',', $value);
    }

    /**
     * @param $value
     */
    public function setModeratorsAttribute($value)
    {
        $this->attributes['moderators'] = is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * Refresh the thread's comments count.
     *
     * @return $this
     */
    public function refreshThreadCount()
    {
        $category_ids = Category::query()->where('id', $this->id)->orWhere('parentid', $this->id)->pluck('id')->toArray();
        $this->thread_count = Thread::query()
            ->where('is_approved', Thread::APPROVED)
            ->where('is_draft', 0)
            ->where('type', '<>', 13)
            ->whereIn('category_id', $category_ids)
            ->whereNull('deleted_at')
            ->whereNotNull('user_id')
            ->count();

        $categoryDetail = Category::query()->where('id', $this->id)->first();
        if ($categoryDetail->parentid !== 0) {
            $father_category_ids = Category::query()->where('id', $categoryDetail->parentid)->orWhere('parentid', $categoryDetail->parentid)->pluck('id')->toArray();
            $categoryFatherDetail = Category::query()->where('id', $categoryDetail->parentid)->first();
            $categoryFatherDetail->thread_count = Thread::query()
                ->where('is_approved', Thread::APPROVED)
                ->where('is_draft', 0)
                
                ->whereIn('category_id', $father_category_ids)
                ->whereNull('deleted_at')
                ->whereNotNull('user_id')
                ->count();
            $categoryFatherDetail->save();
        }

        return $this;
    }

    /**
     * Define the relationship with the category's threads.
     *
     * @return HasMany
     */
    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    /**
     * @param User $user
     * @param string $permission
     * @param bool $condition
     * @return array
     */
    protected static function getIdsWherePermission(User $user, string $permission, bool $condition = true): array
    {
        static $categories;

        if (!$categories) {
            $categories = static::all();
        }

        $hasGlobalPermission = $user->hasPermission($permission);

        $canForCategory = function (self $category) use ($user, $permission, $hasGlobalPermission) {
            return $user->hasPermission('switch.' . $permission)
                && ($hasGlobalPermission || $user->hasPermission('category' . $category->id . '.' . $permission));
        };

        $ids = [];

        foreach ($categories as $category) {
            if ($canForCategory($category) === $condition) {
                $ids[] = $category->id;
            }
        }

        return $ids;
    }

    public static function getIdsWhereCan(User $user, string $permission): array
    {
        return static::getIdsWherePermission($user, $permission, true);
    }

    public static function getIdsWhereCannot(User $user, string $permission): array
    {
        return static::getIdsWherePermission($user, $permission, false);
    }

    public function hasThreads($id)
    {
        $category = Category::query()->findOrFail($id);

        $childCategoryIds = array();
        if ($category->parentid == 0) {
            $childCategoryIds = Category::query()->where('parentid', $category->id)->pluck('id')->toArray();
        }

        $threads = Thread::query()
            ->where('category_id', $id)
            ->orWhereIn('category_id', $childCategoryIds)
            ->where('is_approved', Thread::APPROVED)
            ->where('is_draft', Thread::IS_NOT_DRAFT)
            ->whereNull('deleted_at')
            ->whereNotNull('user_id')
            ->get()->toArray();
        if (!empty($threads)) {
            return true;
        }
        return false;
    }

    public function getValidCategoryIds(User $user, $categoryids = [])
    {
        $groups = $user->groups->toArray();
        if (empty($groups)) {
            return false;
        }
        $groupIds = array_column($groups, 'id');
        $permissions = Permission::categoryPermissions($groupIds);
        $cids = self::query()->pluck('id')->toArray();
        $p = [];
        if ($user->isAdmin()) {
            $p = $cids;
        } else {
            foreach ($cids as $cid) {
                $viewThread = 'category' . $cid . '.viewThreads';
                in_array($viewThread, $permissions) && $p[] = $cid;
            }
            foreach ($cids as $cid) {
                $viewThread = 'category' . $cid . '.viewaskThreads';
                in_array($viewThread, $permissions) && $p[] = $cid;
            }
            foreach ($cids as $cid) {
                $viewThread = 'category' . $cid . '.viewkechengThreads';
                in_array($viewThread, $permissions) && $p[] = $cid;
            }
            if (in_array('viewThreads', $permissions)) {
                $p = $cids;
            }
        }
        if (empty($categoryids)) {
            $categoryids = $p;
        } else {
            $categoryids = array_intersect($categoryids, $p);
        }
        return $categoryids;
    }
}
