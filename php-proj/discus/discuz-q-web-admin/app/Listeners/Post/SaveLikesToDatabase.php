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

namespace App\Listeners\Post;

use App\Commands\RedPacket\CountLikedMakeRedPacket;
use App\Events\Post\Deleted;
use App\Events\Post\Saving;
use App\Notifications\Liked;
use Carbon\Carbon;
use Discuz\Auth\AssertPermissionTrait;
use Discuz\Auth\Exception\NotAuthenticatedException;
use Discuz\Auth\Exception\PermissionDeniedException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Support\Arr;
use App\Models\UserIntegralLog;
use App\Models\Setting;
/**
 * @property Dispatcher events
 */
class SaveLikesToDatabase
{
    /**
     * @var Dispatcher
     */
    protected $bus;

    use AssertPermissionTrait;

    public function __construct(BusDispatcher $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $this->events = $events;
        $events->listen(Saving::class, [$this, 'whenPostIsSaving']);
        $events->listen(Deleted::class, [$this, 'whenPostIsDeleted']);
    }

    /**
     * @param Saving $event
     * @param BusDispatcher $bus
     * @throws NotAuthenticatedException
     * @throws PermissionDeniedException
     */
    public function whenPostIsSaving(Saving $event)
    {
        $post = $event->post;
        $actor = $event->actor;
        $data = $event->data;
        // 张安冠 2022-10-05 获取点赞参数
        $setting = Setting::query()->where('key', 'site_integral')->get();
        $integrals = json_decode($setting[0]['value'], true);
        $integral = $integrals[3]['ivalue'];
        $unintegral = $integrals[3]['unvalue'];
        
        // 张安冠 2022-10-05 获取成长值参数
        $setting = Setting::query()->where('key', 'site_growup')->get();
        $growups = json_decode($setting[0]['value'], true);
        $growup = $growups[3]['ivalue'];
        $ungrowup = $growups[3]['unvalue'];
        
        
        $this->assertRegistered($actor);

        if ($post->exists && isset($data['attributes']['isLiked'])) {
            $this->assertCan($actor, 'like', $post);

            $isLiked = $actor->likedPosts()->where('post_id', $post->id)->exists();
            
            // 张安冠 2022-10-05 点赞积分增加
            $userInteheation = new UserIntegralLog();
            $userInteheation->user_id = $actor->id;

            if ($isLiked) {
                //点赞
                // 已喜欢且 isLiked 为 false 时，取消喜欢
                if (! $data['attributes']['isLiked']) {
                    $actor->likedPosts()->detach($post->id);

                    $post->refreshLikeCount()->save();
                }
                // 判断积分开关，0为关闭。大于0开启
                if ($unintegral>0){
                    $userInteheation->change_desc = "取消点赞扣取"; 
                    $userInteheation->change_type = 2; // change_type=2 代表取消
                    $userInteheation->change_value = -$unintegral; // 积分数据记录
                }
                if ($unintegral>0){
                    $actor->integral_count = $actor->integral_count - $unintegral; // 个人积分减除
                }
                if ($ungrowup>0){
                    $actor->growup_count = $actor->growup_count - $ungrowup; // 成长值
                }
            } else {
                // 未喜欢且 isLiked 为 true 时，设为喜欢
                if ($data['attributes']['isLiked']) {
                    $actor->likedPosts()->attach($post->id, ['created_at' => Carbon::now()]);
                    
                    $post->refreshLikeCount()->save();
                    

                    //根据点赞数获取红包
                    $this->bus->dispatch(new CountLikedMakeRedPacket($event->post->thread->user,$event->post->user,$event->actor,$event->post));

                    // 如果被点赞的用户不是当前用户，则通知被点赞的人
                    if ($post->user->id != $actor->id) {
                        // Tag 发送通知
                        $post->user->notify(new Liked($actor, clone $post));
                    }
                
                    //$integral = UserIntegralLog::query()->insert(['key' => $key, 'value' => $val, 'tag' => 'default']);
                    //$actor->integralPosts()->attach($post->id, ['created_at' => Carbon::now()]);
                    
                }
                
                if ($integral>0){
                    $userInteheation->change_desc = "点赞奖励";
                    $userInteheation->change_type = 1; // change_type=1 代表奖
                    $userInteheation->change_value = $integral; // 增加积分记录
                    $actor->integral_count = $actor->integral_count + $integral; // 个人积分增加
                }
                if ($growup>0){
                    $actor->growup_count = $actor->growup_count +$growup; // 成长值
                }
                
            }
            if ($growup>0 || $integral>0 || $ungrowup>0 || $unintegral>0){
                // 添加积分
                $userInteheation->save();
            }
            

            // 刷新用户点赞数
            $actor->refreshUserLiked();
            $actor->save();
        }
    }

    /**
     * @param Deleted $event
     */
    public function whenPostIsDeleted(Deleted $event)
    {
        $event->post->likedUsers()->detach();
    }
}
