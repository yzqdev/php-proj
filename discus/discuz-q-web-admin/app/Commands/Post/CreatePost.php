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

namespace App\Commands\Post;

use App\Censor\Censor;
use App\Events\Post\Created;
use App\Events\Post\Saved;
use App\Events\Post\Saving;
use App\Models\Post;
use App\Models\PostMod;
use App\Models\Thread;
use App\Models\User;
use App\Repositories\ThreadRepository;
use App\Validators\PostValidator;
use Carbon\Carbon;
use Discuz\Auth\AssertPermissionTrait;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Foundation\EventsDispatchTrait;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreatePost
{
    use AssertPermissionTrait;
    use EventsDispatchTrait;

    const LIMIT_RED_PACKET_TIME = 30;
    /**
     * The id of the thread.
     *
     * @var int
     */
    public $threadId;

    /**
     * The id of the post waiting to be replied.
     *
     * @var int
     */
    public $replyPostId;

    /**
     * The id of the post waiting to be replied.
     *
     * @var int
     */
    public $replyUserId;

    /**
     * The id of the post waiting to be replied.
     *
     * @var int
     */
    public $commentPostId;

    /**
     * The id of the post waiting to be replied.
     *
     * @var int
     */
    public $commentUserId;

    /**
     * The user performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * The attributes of the new thread.
     *
     * @var array
     */
    public $data;

    /**
     * The current ip address of the actor.
     *
     * @var string
     */
    public $ip;

    /**
     * The current port of the actor.
     *
     * @var int
     */
    public $port;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var null
     */
    protected $isFirst;

    /**
     * @param int $threadId
     * @param User $actor
     * @param array $data
     * @param string $ip
     * @param int $port
     * @param null $isFirst
     */
    public function __construct($threadId, User $actor, array $data, $ip, $port, $isFirst = null)
    {
        $this->threadId = $threadId;
        $this->replyPostId = Arr::get($data, 'attributes.replyId', null);
        $this->commentPostId = Arr::get($data, 'attributes.commentPostId', null);
        $this->actor = $actor;
        $this->data = $data;
        $this->ip = $ip;
        $this->port = $port;
        $this->isFirst = $isFirst;
    }

    /**
     * @param Dispatcher $events
     * @param ThreadRepository $threads
     * @param PostValidator $validator
     * @param Censor $censor
     * @param Post $post
     * @return Post
     * @throws PermissionDeniedException
     * @throws Exception
     * @throws ValidationException
     * @throws GuzzleException
     */
    public function handle(Dispatcher $events, ThreadRepository $threads, PostValidator $validator, Censor $censor, Post $post)
    {
        $cache = app('cache');
        $this->events = $events;
        $types = Arr::get($this->data, 'relationships.thread.data.types');

        $thread = $threads->findOrFail($this->threadId);

        if($thread->is_red_packet != Thread::NOT_HAVE_RED_PACKET && (Carbon::now()->timestamp - $thread->created_at->timestamp > 30)){
            $cacheKey = 'thread_red_packet_'.md5($this->actor->id);
            $red_cache = $cache->get($cacheKey);
            if($red_cache){
                $cache->put($cacheKey, true, self::LIMIT_RED_PACKET_TIME);
                throw new Exception(trans('user.do_frequent'));
            }
        }
        //$thread->setLastPost($post);


        $isFirst = is_null($this->isFirst) ? empty($thread->post_count):$this->isFirst;

        if ($isFirst && ($firstPost = $thread->firstPost)) {
            $post = $firstPost;
        }


        if (!$isFirst) {
            //????????????
            if($types == 14) {
                 // ????????????????????????????????????
            $this->assertCan($this->actor, 'askreply', $thread);
            } else {
                 // ????????????????????????????????????
            $this->assertCan($this->actor, 'reply', $thread);
            }



            // ????????????????????????????????????????????????
            if (! empty($this->commentPostId)) {
                /** @var Post $comment */
                $comment = $post->newQuery()
                    ->where('id', $this->commentPostId)
                    ->where('thread_id', $thread->id)
                    ->first(['user_id', 'reply_post_id']);

                $this->commentUserId = $comment->user_id;
                $this->replyPostId = $comment->reply_post_id;

                if (! $this->commentUserId) {
                    throw new ModelNotFoundException;
                }
            }

            // ????????????
            if (! empty($this->replyPostId)) {
                // ?????????????????????????????????????????????
                $this->replyUserId = $post->newQuery()
                    ->where('id', $this->replyPostId)
                    ->where('thread_id', $thread->id)
                    ->value('user_id');

                if (! $this->replyUserId) {
                    throw new ModelNotFoundException;
                }
            }

        }

        $post = $post->reply(
            $thread->id,
            trim(Arr::get($this->data, 'attributes.content')),
            $this->actor->id,
            $this->ip,
            $this->port,
            $this->replyPostId,
            $this->replyUserId,
            $this->commentPostId,
            $this->commentUserId,
            $isFirst,
            (bool) Arr::get($this->data, 'attributes.isComment'),
            $post
        );

        // first post ????????????????????? CreateThread ???????????????
        if (!$isFirst) {
            $post->content = $censor->checkText($post->content);
        }

        $content = $post->content;

        if(mb_strlen($post->content)>49999){
            throw new \Exception('??????????????????');
        }
        // ???????????????????????????????????????????????????
        if ($censor->isMod) {
            $post->is_approved = Post::UNAPPROVED;
        } else {
            $post->is_approved = Post::APPROVED;
        }

        $post->raise(new Created($post, $this->actor, $this->data));

        $this->events->dispatch(
            new Saving($post, $this->actor, $this->data)
        );

        if (!$isDraft = Arr::get($this->data, 'attributes.is_draft')) {
            $validator->valid($post->getAttributes());
        }
        //????????????????????????-2022-10-11 ?????????
        Thread::query()->where('id', $thread->id)->update(['posted_at' => $post->created_at]);
        //$thread->timestamps = false;

        $post->save();

        //??????????????????????????????????????????????????????????????????????????????
        if($thread->is_red_packet != Thread::NOT_HAVE_RED_PACKET && (Carbon::now()->timestamp - $thread->created_at->timestamp > 30)){
            $cacheKey = 'thread_red_packet_'.md5($this->actor->id);
            $cache->put($cacheKey, true, self::LIMIT_RED_PACKET_TIME);
        }

        // ????????????????????????
        if (!$isDraft = Arr::get($this->data, 'attributes.is_draft')) {
            if ($post->is_approved === Post::UNAPPROVED && $censor->wordMod) {
                $stopWords = new PostMod;
                $stopWords->stop_word = implode(',', array_unique($censor->wordMod));

                $post->stopWords()->save($stopWords);
            }
        }

        $post->raise(new Saved($post, $this->actor, $this->data));

        // TODO: ?????????????????????????????????????????????????????????????????????????????????????????????
        // $this->notifications->onePerUser(function () use ($post, $actor) {
        $this->dispatchEventsFor($post, $this->actor);
        // });


        $post->rewards = floatval(sprintf('%.2f', $post->getPostReward()));
        $post->content != $content && $post->content = $content;
        return $post;
    }
}
