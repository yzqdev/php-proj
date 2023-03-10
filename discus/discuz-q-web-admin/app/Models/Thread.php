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

use App\Common\CacheKey;
use App\Common\Utils;
use App\Events\Thread\Hidden;
use App\Events\Thread\Restored;
use App\Models\ThreadReward;
use Carbon\Carbon;
use DateTime;
use Discuz\Auth\Anonymous;
use Discuz\Base\DzqModel;
use Discuz\Common\PubEnum;
use Discuz\Database\ScopeVisibilityTrait;
use Discuz\Foundation\EventGeneratorTrait;
use Discuz\SpecialChar\SpecialCharServer;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/**
 * @property int $id
 * @property int $user_id
 * @property int $last_posted_user_id
 * @property int $category_id
 * @property int $type
 * @property string $title
 * @property float $price
 * @property float $attachment_price
 * @property int $free_words
 * @property int $post_count
 * @property int $view_count
 * @property int $paid_count
 * @property int $rewarded_count
 * @property float $longitude
 * @property float $latitude
 * @property string $address
 * @property string $location
 * @property Carbon posted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $deleted_user_id
 * @property int $is_approved
 * @property bool|null $is_paid
 * @property bool|null $is_paid_attachment
 * @property bool $is_sticky
 * @property bool $is_essence
 * @property bool $is_site
 * @property bool $is_anonymous
 * @property bool $is_display
 * @property Post $firstPost
 * @property Collection $topic
 * @property Collection $orders
 * @property User $user
 * @property Category $category
 * @property threadVideo $threadVideo
 * @property Question $question
 * @property Order $onlookerState
 * @property int $is_red_packet
 * @property int $rule
 * @property int $condition
 * @property int $likenum
 * @property int $money
 * @property int $number
 * @property int $remain_money
 * @property int $remain_number
 * @property int $is_draft
 * @method increment($column, $amount = 1, array $extra = [])
 * @method decrement($column, $amount = 1, array $extra = [])
 */
class Thread extends DzqModel
{
    use EventGeneratorTrait;
    use ScopeVisibilityTrait;

    const TYPE_OF_TEXT = 0;

    const TYPE_OF_LONG = 1;

    const TYPE_OF_VIDEO = 2;

    const TYPE_OF_IMAGE = 3;

    const TYPE_OF_AUDIO = 4;

    const TYPE_OF_QUESTION = 5;

    const TYPE_OF_GOODS = 6;
    
    //const TYPE_OF_QUAN = 7; // ??????
    const TYPE_OF_QUAN = 10; // ??????
    
    const TYPE_OF_QUANTIE = 11; // ??????
    
    const TYPE_OF_HAIBAO = 8; // ????????????
    
    const TYPE_OF_KE = 12; // ??????
    
    const TYPE_OF_KETIE = 13; // ??????
    
    const TYPE_OF_ASK = 14; // ?????? 2022-10-20

    const UNAPPROVED = 0;

    const APPROVED = 1;

    const IGNORED = 2;

    const NOT_HAVE_RED_PACKET = 0;

    const HAVE_RED_PACKET = 1;

    const THREAD_VIDEO_STATUS_TRANSCODING = 0; //?????????

    const THREAD_VIDEO_STATUS_COMPLETE = 1; //????????????

    const THREAD_VIDEO_STATUS_FAIL = 2; //????????????

    /**
     * ????????????????????????(???)
     */
    const CONTENT_LENGTH = 80;

    const  SORT_BY_THREAD = 1;
    const  SORT_BY_CREAT = 2;
    const  SORT_BY_POST = 3;

    /**
     * ??????
     */
    const IS_NOT_DRAFT = 0;

    const IS_DRAFT = 1;

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'type' => 'integer',
        'quanid' => 'integer',
        'keid' => 'integer',
        'price' => 'decimal:2',
        'free_words' => 'float',
        'is_sticky' => 'boolean',
        'is_poster' => 'boolean',
        'is_pinglun' => 'boolean',
        'is_original' => 'boolean',
        'is_essence' => 'boolean',
        'is_anonymous' => 'boolean',
        'is_display' => 'boolean',
    ];

    /**
     * {@inheritdoc}
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    const EXT_TEXT = 'text';
    const EXT_LONG = 'thread';
    const EXT_VIDEO = 'video';
    const EXT_IMAGE = 'image';
    const EXT_AUDIO = 'audio';
    const EXT_QA = 'question';
    const EXT_GOODS = 'goods';
    const EXT_ASK = 'ask';

    public $threadDic = [
        self::TYPE_OF_TEXT => '??????',
        self::TYPE_OF_LONG => '??????',
        self::TYPE_OF_VIDEO => '??????',
        self::TYPE_OF_IMAGE => '??????',
        self::TYPE_OF_AUDIO => '??????',
        self::TYPE_OF_QUESTION => '??????',
        self::TYPE_OF_GOODS => '??????',
        self::TYPE_OF_HAIBAO => '??????',
        self::TYPE_OF_QUAN => '??????',
        self::TYPE_OF_QUANTIE => '??????',
        self::TYPE_OF_KE => '??????',
        self::TYPE_OF_KETIE => '??????',
        self::TYPE_OF_ASK => '??????',
    ];


    /**
     * The user for which the state relationship should be loaded.
     *
     * @var User
     */
    protected static $stateUser;

    /**
     * ?????????????????????????????????????????? id ?????????????????? id ??????????????????
     *
     * @var Collection
     */
    protected static $userHasPaidThreads;

    protected static $userHasPaidThreadAttachments;

    /**
     * datetime ????????????
     *
     * @param $timeAt
     * @return string
     */
    public function formatDate($timeAt)
    {
        return $this->{$timeAt}->format(DateTime::RFC3339);
    }

    /**
     * Hide the thread.
     *
     * @param User $actor
     * @param array $options
     * @return $this
     */
    public function hide(User $actor, $options = [])
    {
        if (! $this->deleted_at) {
            $this->deleted_at = Carbon::now();
            $this->deleted_user_id = $actor->id;

            $this->raise(new Hidden($this, $actor, $options));
        }

        return $this;
    }

    /**
     * Restore the thread.
     *
     * @param User $actor
     * @param array $options
     * @return $this
     */
    public function restore(User $actor, $options = [])
    {
        if ($this->deleted_at !== null) {
            $this->deleted_at = null;
            $this->deleted_user_id = null;

            $this->raise(new Restored($this, $actor, $options));
        }

        return $this;
    }

    /**
     * ?????????????????? Thread content
     *
     * @param int $substr
     * @param bool $parse
     * @return Stringable|string
     */
    public function getContentByType($substr, $parse = false)
    {
        $special = app(SpecialCharServer::class);

        if ($this->type == 1) {
            $firstPost = $substr ? Str::of($this->title)->substr(0, $substr) : $this->title;
            $firstPost = $special->purify($firstPost);
        } else {
            // ?????????????????????????????????????????????
            $this->firstPost->content = $substr ? Str::of($this->firstPost->content)->substr(0, $substr) : $this->firstPost->content;
            if ($parse) {
                // ??????
                $firstPost = $this->firstPost->content;
            } else {
                $firstPost = $this->firstPost->formatContent();
            }
        }

        return $firstPost;
    }

    /**
     * Get the last three posts of the thread.
     *
     * @return Collection
     */
    public function lastThreePosts()
    {
        return $this->posts()
            ->where('is_first', false)
            ->limit(3)
            ->get();
    }

    /**
     * Set the thread's last post details.
     *
     * @param Post $post
     * @return $this
     */
    public function setLastPost(Post $post)
    {
        $this->last_posted_user_id = $post->user_id;
        $this->updated_at = $post->created_at->gt($this->updated_at) ? $post->created_at : $this->updated_at;
        if ($this->type < 0) {
          // $this->posted_at = $post->created_at;
        }
        return $this;
    }

    /**
     * Refresh a thread's last post details.
     *
     * @return $this
     */
    public function refreshLastPost()
    {
        /** @var Post $lastPost */
        if ($lastPost = $this->replies()->latest()->first()) {
            $this->setLastPost($lastPost);
        }

        return $this;
    }

    /**
     * Refresh the thread's post count.
     *
     * @return $this
     */
    public function refreshPostCount()
    {
        $this->post_count = $this->posts()
            ->where('is_first', false)
            ->where('is_comment', false)
            ->where('is_approved', Post::APPROVED)
            ->whereNull('deleted_at')
            ->whereNotNull('user_id')
            ->count() + 1;  // include first post

        return $this;
    }

    /**
     * ??????????????????
     *
     * @return $this
     */
    public function refreshPaidCount()
    {
        $this->paid_count = $this->orders()
            ->whereIn('type', [Order::ORDER_TYPE_THREAD, Order::ORDER_TYPE_ATTACHMENT])
            ->where('status', Order::ORDER_STATUS_PAID)
            ->count();

        return $this;
    }

    /**
     * ??????????????????
     *
     * @return $this
     */
    public function refreshRewardedCount()
    {
        $this->rewarded_count = $this->orders()
            ->where('type', Order::ORDER_TYPE_REWARD)
            ->where('status', Order::ORDER_STATUS_PAID)
            ->count();

        return $this;
    }

    /**
     * Define the relationship with the thread's publicly-visible posts.
     *
     * @return HasMany
     */
    public function replies()
    {
        return $this->posts()->where('is_approved', Thread::APPROVED);
    }

    /**
     * Define the relationship with the thread's first post.
     *
     * @return HasOne
     */
    public function firstPost()
    {
        return $this->hasOne(Post::class)->where('is_first', true);
    }

    /**
     * Define the relationship with the thread's first post.
     *
     * @return hasMany
     */
    public function rewarded()
    {
        return $this->hasMany(Order::class, 'type_id')
            ->where('type', 2)
            ->where('status', 1);
    }

    /**
     * Define the relationship with the thread's category.
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Define the relationship with the thread's author.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship with the thread's author.
     *
     * @return BelongsTo
     */
    public function lastPostedUser()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship with the user who hid the post.
     *
     * @return BelongsTo
     */
    public function deletedUser()
    {
        return $this->belongsTo(User::class, 'deleted_user_id')->withDefault([
            'username' => trans('user.user_has_deleted'),
        ]);
    }

    /**
     * Define the relationship with the thread's posts.
     *
     * @return HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Define the relationship with the thread's orders.
     *
     * @return HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * ????????????????????????????????????
     * ??????????????????1?????????2?????????3???????????????4??????????????????5?????????????????????6?????????????????????7???????????????
     *
     * @param int $type
     * @param bool $more
     * @return Collection|Order|Model
     */
    public function ordersByType($type, $more = true)
    {
        $query = $this->orders()->where('type', $type);

        return $more ? $query->get() : $query->first();
    }

    /**
     * Define the relationship with the thread's operation Log.
     */
    public function logs()
    {
        return $this->morphMany(UserActionLogs::class, 'log_able');
    }

    /**
     * Define the relationship with the thread's favorite users.
     *
     * @return BelongsToMany
     */
    public function favoriteUsers()
    {
        return $this->belongsToMany(User::class)->withPivot('created_at');
    }

    public function threadVideo()
    {
        return $this->hasOne(ThreadVideo::class)->where('type', ThreadVideo::TYPE_OF_VIDEO);
    }

    public function threadAudio()
    {
        return $this->hasOne(ThreadVideo::class)->where('type', ThreadVideo::TYPE_OF_AUDIO);
    }

    public function topic()
    {
        return $this->belongsToMany(Topic::class)->withPivot('created_at');
    }

    public function threadTopic()
    {
        return $this->hasMany(ThreadTopic::class);
    }

    public function question()
    {
        $questionType = ThreadReward::query()->where('thread_id', $this->id)->first();
        if(isset($questionType['type']) && $questionType['type'] == 0) {
            return $this->hasOne(ThreadReward::class);
        } else{
            return $this->hasOne(Question::class);
        }
    }

    public function redPacket()
    {
        return $this->hasOne(RedPacket::class);
    }

    /**
     * ?????????????????????
     *
     * @return string
     */
    public function isAnonymousName()
    {
        return $this->is_anonymous ? (new Anonymous)->getUsername() : $this->user->username;
    }

    /**
     * Set the user for which the state relationship should be loaded.
     *
     * @param User $user
     * @param Collection|null $threads
     */
    public static function setStateUser(User $user, Collection $threads = null)
    {
        static::$stateUser = $user;

        // ????????????????????????????????????????????????
        if ($threads) {
            foreach ([Order::ORDER_TYPE_THREAD, Order::ORDER_TYPE_ATTACHMENT] as $type) {
                $data = [];
                $orders = Order::query()
                    ->whereIn('thread_id', $threads->pluck('id'))
                    ->where('user_id', $user->id)
                    ->where('status', Order::ORDER_STATUS_PAID)
                    ->where('type', $type)
                    ->pluck('thread_id');

                $data[$user->id] = $threads->keyBy('id')
                    ->map(function ($thread) use ($orders) {
                        return $orders->contains($thread->id);
                    });
                if ($type == Order::ORDER_TYPE_THREAD) {
                    // ??????????????????
                    static::$userHasPaidThreads = $data;
                } elseif ($type == Order::ORDER_TYPE_ATTACHMENT) {
                    // ????????????????????????
                    static::$userHasPaidThreadAttachments = $data;
                }
            }
        }
    }

    /**
     * Define the relationship with the thread's favorite state for a particular user.
     *
     * @param User|null $user
     * @return HasOne
     */
    public function favoriteState(User $user = null)
    {
        $user = $user ?: static::$stateUser;

        return $this->hasOne(ThreadUser::class)->where('user_id', $user ? $user->id : null);
    }

    /**
     * Define the relationship with the question's onlooker state for a particular user.
     *
     * @param User|null $user
     * @return HasOne
     */
    public function onlookerState(User $user = null)
    {
        $user = $user ?: static::$stateUser;

        return $this->hasOne(Order::class)
            ->where('orders.user_id', $user ? $user->id : null)
            ->where('orders.status', Order::ORDER_STATUS_PAID)
            ->where('orders.type', Order::ORDER_TYPE_ONLOOKER);
    }

    /**
     * ???????????????????????????????????????
     *
     * @return bool|null
     */
    public function getIsPaidAttribute()
    {
        $user = static::$stateUser;

        // ???????????????
        if (! $user) {
            throw new \RuntimeException('You must set the user with setStateUser()');
        }

        // ????????????????????? null
        if ($this->price <= 0) {
            return null;
        }

        // ????????????????????????
        if ($user->can('freeViewPosts.' . $this->type, $this)) {
            return true;
        }

        // ????????????????????? false
        if (! $user->exists) {
            return false;
        }

        // ???????????? ??? ????????? ?????? true
        if ($this->user_id === $user->id || $user->isAdmin()) {
            return true;
        }

        // ??????????????????????????????????????? N + 1 ?????????
        if (isset(static::$userHasPaidThreads[$user->id][$this->id])) {
            return static::$userHasPaidThreads[$user->id][$this->id];
        }

        $isPaid = Order::query()
            ->where('user_id', $user->id)
            ->where('thread_id', $this->id)
            ->where('status', Order::ORDER_STATUS_PAID)
            ->where('type', Order::ORDER_TYPE_THREAD)
            ->exists();
        static::$userHasPaidThreads[$user->id][$this->id] = $isPaid;

        return $isPaid;
    }

    /**
     * ????????????????????????
     *
     * @return bool|null
     */
    public function getIsPaidAttachmentAttribute()
    {
        $user = static::$stateUser;

        // ???????????????
        if (! $user) {
            throw new \RuntimeException('You must set the user with setStateUser()');
        }

        // ????????????????????? null
        if ($this->attachment_price <= 0) {
            return null;
        }

        // ????????????????????????
        if ($user->can('freeViewPosts.' . $this->type, $this)) {
            return true;
        }

        // ????????????????????? false
        if (! $user->exists) {
            return false;
        }

        // ???????????? ??? ????????? ?????? true
        if ($this->user_id === $user->id || $user->isAdmin()) {
            return true;
        }

        // ??????????????????????????????????????? N + 1 ?????????
        if (isset(static::$userHasPaidThreadAttachments[$user->id][$this->id])) {
            return static::$userHasPaidThreadAttachments[$user->id][$this->id];
        }

        $isPaidAttachment = Order::query()
            ->where('user_id', $user->id)
            ->where('thread_id', $this->id)
            ->where('status', Order::ORDER_STATUS_PAID)
            ->where('type', Order::ORDER_TYPE_ATTACHMENT)
            ->exists();
        static::$userHasPaidThreadAttachments[$user->id][$this->id] = $isPaidAttachment;

        return $isPaidAttachment;
    }

    public function save(array $options = [])
    {
        $this->deleteCacheKey();
        return parent::save($options); // TODO: Change the autogenerated stub
    }
    public function update(array $attributes = [], array $options = [])
    {
        $this->deleteCacheKey();
        return parent::update($attributes, $options); // TODO: Change the autogenerated stub
    }
    public function delete()
    {
        $this->deleteCacheKey();
        return parent::delete(); // TODO: Change the autogenerated stub
    }

    private function deleteCacheKey()
    {
        $cache = app('cache');
        $cache->forget(CacheKey::LIST_V2_THREADS);
        //??????????????????
        $cache->forget(CacheKey::THREAD_RESOURCE_BY_ID . $this->id);
        $keys = $cache->get(CacheKey::LIST_THREAD_KEYS);
        if (empty($keys)) {
            return false;
        }
        $keys = json_decode($keys, true);

        foreach ($keys as $key) {
            $cache->forget($key);
        }
        $cache->forget(CacheKey::LIST_THREAD_KEYS);
        return true;
    }

    public function formatThread($thread)
    {
        $data = [
            'pid' => $thread['id'],
            'type' => $thread['type'],
            'categoryId' => $thread['category_id'],
            'title' => $thread['title'],
            'price' => $thread['price'],
            'attachmentPrice' => $thread['attachment_price'],
            'postCount' => $thread['post_count'] - 1,
            'viewCount' => $thread['view_count'],
            'rewardedCount' => $thread['rewarded_count'],
            'paidCount' => $thread['paid_count'],
            'address' => $thread['address'],
            'location' => $thread['location'],
            'createdAt' => date('Y-m-d H:i:s', strtotime($thread['created_at'])),
            'diffCreatedAt' => Utils::diffTime($thread['created_at']),
            'isRedPacket' => $thread['is_red_packet'],
            'extension' => null
        ];
        switch ($thread['type']) {
            case Thread::TYPE_OF_IMAGE:
            case Thread::TYPE_OF_AUDIO:
                $data['title'] = Post::instance()->getContentSummary($thread['id']);
                break;
            case Thread::TYPE_OF_VIDEO:
                $data['title'] = Post::instance()->getContentSummary($thread['id']);
                $data['extension'] = [
                    Thread::EXT_VIDEO => ThreadVideo::instance()->getThreadVideo($thread['id'])
                ];
                break;
            case Thread::TYPE_OF_GOODS:
                $postId = true;
                $data['title'] = Post::instance()->getContentSummary($thread['id'], $postId);;
                $data['extension'] = [
                    Thread::EXT_GOODS => PostGoods::instance()->getPostGoods($postId)
                ];
                break;
            case Thread::TYPE_OF_QUESTION:
                $data['title'] = Post::instance()->getContentSummary($thread['id']);
                $data['extension'] = [
                    Thread::EXT_QA => Question::instance()->getQuestions($thread['id'])
                ];
                break;
            default:
                break;
        }
        return $data;
    }
    /**
     * @desc ??????????????????????????????????????????
     * @param $linkString
     * @return array[]
     */
    public function getReplaceString($linkString)
    {
        preg_match_all('/:[a-z]+:/i', $linkString, $m1);
        preg_match_all('/@.+? /', $linkString, $m2);
        preg_match_all('/#.+?#/', $linkString, $m3);
        $m1 = array_unique($m1[0]);
        $m2 = array_unique($m2[0]);
        $m3 = array_unique($m3[0]);
        $m2 = str_replace(['@', ''], '', $m2);
        $m3 = str_replace('#', '', $m3);
        $search = [];
        $replace = [];
        $emojis = Emoji::query()->select('code', 'url')->whereIn('code', $m1)->get()->map(function ($item) use ($search) {
            $item['url'] = Utils::getDzqDomain() . '/' . $item['url'];
            $item['html'] = sprintf('<img style="display:inline-block;vertical-align:top" src="%s" alt="ciya" class="qq-emotion">', $item['url']);
            return $item;
        })->toArray();
        $ats = User::query()->select('id', 'username')->whereIn('username', $m2)->get()->map(function ($item) {
            $item['username'] = '@' . $item['username'];
            $item['html'] = sprintf('<span id="member" value="%s">%s</span>', $item['id'], $item['username']);
            return $item;
        })->toArray();
        $topics = Topic::query()->select('id', 'content')->whereIn('content', $m3)->get()->map(function ($item) {
            $item['content'] = '#' . $item['content'] . '#';
            $item['html'] = sprintf('<span id="topic" value="%s">%s</span>', $item['id'], $item['content']);
            return $item;
        })->toArray();
        foreach ($emojis as $emoji) {
            $search[] = $emoji['code'];
            $replace[] = $emoji['html'];
        }
        foreach ($ats as $at) {
            $search[] = $at['username'];
            $replace[] = $at['html'];
        }
        foreach ($topics as $topic) {
            $search[] = $topic['content'];
            $replace[] = $topic['html'];
        }
        return [$search, $replace];
    }
}
