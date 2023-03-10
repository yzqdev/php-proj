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

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use App\Notifications\Messages\Database\GroupMessage;
use App\Notifications\Messages\Database\PostMessage;
use App\Notifications\Messages\Database\RegisterMessage;
use App\Notifications\Messages\Database\StatusMessage;
use App\Notifications\Messages\MiniProgram\GroupMiniProgramMessage;
use App\Notifications\Messages\MiniProgram\PostMiniProgramMessage;
use App\Notifications\Messages\MiniProgram\RegisterMiniProgramMessage;
use App\Notifications\Messages\MiniProgram\StatusMiniProgramMessage;
use App\Notifications\Messages\Sms\GroupSmsMessage;
use App\Notifications\Messages\Sms\PostSmsMessage;
use App\Notifications\Messages\Sms\RegisterSmsMessage;
use App\Notifications\Messages\Sms\StatusSmsMessage;
use App\Notifications\Messages\Wechat\GroupWechatMessage;
use App\Notifications\Messages\Wechat\PostWechatMessage;
use App\Notifications\Messages\Wechat\RegisterWechatMessage;
use App\Notifications\Messages\Wechat\StatusWechatMessage;
use Discuz\Notifications\Messages\SimpleMessage;
use Discuz\Notifications\NotificationManager;
use Exception;
use Illuminate\Support\Collection;

class System extends AbstractNotification
{
    public $actor;

    public $data;

    protected $message;

    /**
     * @var array
     */
    public $tplId;

    /**
     * @var Collection
     */
    protected $messageRelationship;

    public function __construct($message, User $actor, $data = [])
    {
        $this->message = app($message);

        $this->actor = $actor;
        $this->data = $data;

        /**
         * ?????????????????????????????????????????? tplId
         */
        $this->initNoticeMessage();

        $this->setTemplate();
    }

    /**
     * ?????????????????????????????????????????????
     * ?????????????????????????????????????????????
     */
    protected function setTemplate()
    {
        self::getTemplate($this->tplId);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // ??????????????????????????????
        return $this->getNotificationChannels();
    }

    public function getTplModel($type)
    {
        return self::$tplData->where('notice_id', $this->tplId[$type])->first();
    }

    /**
     * @param string $type
     * @return SimpleMessage
     */
    public function getMessage(string $type)
    {
        return $this->messageRelationship->get($type);
    }

    public function toDatabase($notifiable)
    {
        $message = $this->getMessage('database');
        $message->setData($this->getTplModel('database'), $this->actor, $this->data);

        return (new NotificationManager)->driver('database')->setNotification($message)->build();
    }

    public function toWechat($notifiable)
    {
        $message = $this->getMessage('wechat');
        $message->setData($this->getTplModel('wechat'), $this->actor, $this->data);

        return (new NotificationManager)->driver('wechat')->setNotification($message)->build();
    }

    public function toSms($notifiable)
    {
        $message = $this->getMessage('sms');
        $message->setData($this->getTplModel('sms'), $this->actor, $this->data);

        return (new NotificationManager)->driver('sms')->setNotification($message)->build();
    }

    public function toMiniProgram($notifiable)
    {
        $message = $this->getMessage('miniProgram');
        $message->setData($this->getTplModel('miniProgram'), $this->actor, $this->data);

        return (new NotificationManager)->driver('miniProgram')->setNotification($message)->build();
    }

    /**
     * ???????????????????????????
     * TODO ????????????????????????????????? Message?????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
     *
     * @see Liked Tag ??????????????????
     */
    protected function initNoticeMessage()
    {
        $this->messageRelationship = collect();
        // init database message
        if (! $this->message instanceof RegisterWechatMessage) {
            $this->messageRelationship['database'] = $this->message;
        }

        // ??????????????????
        if ($this->message instanceof StatusMessage) {
            // set other message relationship
            $this->messageRelationship['wechat'] = app(StatusWechatMessage::class);
            $this->messageRelationship['sms'] = app(StatusSmsMessage::class);
            $this->messageRelationship['miniProgram'] = app(StatusMiniProgramMessage::class);
            // set tpl id
            $this->discTpl($this->actor->status, $this->actor->getRawOriginal('status'));
        } // ?????????????????????
        elseif ($this->message instanceof GroupMessage) {
            // set other message relationship
            $this->messageRelationship['wechat'] = app(GroupWechatMessage::class);
            $this->messageRelationship['sms'] = app(GroupSmsMessage::class);
            $this->messageRelationship['miniProgram'] = app(GroupMiniProgramMessage::class);
            // set tpl id
            $this->tplId = [
                'database'    => 'system.user.group',
                'wechat'      => 'wechat.user.group',
                'sms'         => 'sms.user.group',
                'miniProgram' => 'miniprogram.user.group',
            ];
        } // Post ??????
        elseif ($this->message instanceof PostMessage) {
            // set other message relationship
            $this->messageRelationship['wechat'] = app(PostWechatMessage::class);
            $this->messageRelationship['sms'] = app(PostSmsMessage::class);
            $this->messageRelationship['miniProgram'] = app(PostMiniProgramMessage::class);
            // set tpl id of the notify type
            $this->postTpl();
        } // ????????????
        elseif ($this->message instanceof RegisterMessage || $this->message instanceof RegisterWechatMessage) {
            // ??????????????????????????????????????????????????????????????????????????????????????? openId
            if (! isset($this->data['send_type'])) {
                return;
            }
            // set other message relationship
            if ($this->message instanceof RegisterWechatMessage) {
                $this->messageRelationship['database'] = app(RegisterMessage::class);
                $this->messageRelationship['wechat'] = app(RegisterWechatMessage::class);
            }
            $this->messageRelationship['sms'] = app(RegisterSmsMessage::class);
            $this->messageRelationship['miniProgram'] = app(RegisterMiniProgramMessage::class);
            // set tpl id
            $this->tplId = [
                'database'    => 'system.registered.passed',
                'wechat'      => 'wechat.registered.passed',
                'sms'         => 'sms.registered.passed',
                'miniProgram' => 'miniprogram.registered.passed',
            ];
        }
    }

    /**
     * ????????????
     * (????????????????????? ??? ?????????????????????)
     *
     * @param $status
     * @param $originStatus
     */
    public function discTpl($status, $originStatus)
    {
        if ($status == $originStatus) {
            return;
        }

        if ($status == 0) {
            if ($originStatus == 1) {
                // ????????????????????????
                $this->tplId = [
                    'database'    => 'system.user.normal',
                    'wechat'      => 'wechat.user.normal',
                    'sms'         => 'sms.user.normal',
                    'miniProgram' => 'miniprogram.user.normal',
                ];
            } else {
                // ????????????????????????
                $this->tplId = [
                    'database'    => 'system.registered.approved',
                    'wechat'      => 'wechat.registered.approved',
                    'sms'         => 'sms.registered.approved',
                    'miniProgram' => 'miniprogram.registered.approved',
                ];
            }
        } else {
            if ($originStatus == 0 && $status == 1) {
                // ??????????????????
                $this->tplId = [
                    'database'    => 'system.user.disable',
                    'wechat'      => 'wechat.user.disable',
                    'sms'         => 'sms.user.disable',
                    'miniProgram' => 'miniprogram.user.disable',
                ];
            } elseif ($originStatus == 2 && $status == 3) { // 2????????? ??? ????????????
                // ???????????????????????????
                $this->tplId = [
                    'database'    => 'system.registered.unapproved',
                    'wechat'      => 'wechat.registered.unapproved',
                    'sms'         => 'sms.registered.unapproved',
                    'miniProgram' => 'miniprogram.registered.unapproved',
                ];
            } else {
                // ??????????????????2???????????????????????? ???????????????
                return;
            }
        }
    }

    /**
     * Post ????????????
     */
    public function postTpl()
    {
        /**
         * Post ???????????????????????? ??????????????????
         *
         * @see PostMessage ???????????????????????????????????????????????????
         */
        if (! isset($this->data['notify_type'])) {
            throw new Exception('not_found_post_notify_type');
        }

        switch ($this->data['notify_type']) {
            case Post::NOTIFY_EDIT_CONTENT_TYPE:
                // ??????????????????
                $this->tplId = [
                    'database'    => 'system.post.update',
                    'wechat'      => 'wechat.post.update',
                    'sms'         => 'sms.post.update',
                    'miniProgram' => 'miniprogram.post.update',
                ];
                break;
            case Post::NOTIFY_APPROVED_TYPE:
                // ????????????????????????
                $this->tplId = [
                    'database'    => 'system.post.approved',
                    'wechat'      => 'wechat.post.approved',
                    'sms'         => 'sms.post.approved',
                    'miniProgram' => 'miniprogram.post.approved',
                ];
                break;
            case Post::NOTIFY_UNAPPROVED_TYPE:
                // ?????????????????????/???????????? ??????
                $this->tplId = [
                    'database'    => 'system.post.unapproved',
                    'wechat'      => 'wechat.post.unapproved',
                    'sms'         => 'sms.post.unapproved',
                    'miniProgram' => 'miniprogram.post.unapproved',
                ];
                break;
            case Post::NOTIFY_DELETE_TYPE:
                // ??????????????????
                $this->tplId = [
                    'database'    => 'system.post.deleted',
                    'wechat'      => 'wechat.post.deleted',
                    'sms'         => 'sms.post.deleted',
                    'miniProgram' => 'miniprogram.post.deleted',
                ];
                break;
            case Post::NOTIFY_ESSENCE_TYPE:
                // ??????????????????
                $this->tplId = [
                    'database'    => 'system.post.essence',
                    'wechat'      => 'wechat.post.essence',
                    'sms'         => 'sms.post.essence',
                    'miniProgram' => 'miniprogram.post.essence',
                ];
                break;
            case Post::NOTIFY_STICKY_TYPE:
                // ??????????????????
                $this->tplId = [
                    'database'    => 'system.post.sticky',
                    'wechat'      => 'wechat.post.sticky',
                    'sms'         => 'sms.post.sticky',
                    'miniProgram' => 'miniprogram.post.sticky',
                ];
                break;
            default:
                throw new Exception('post_notify_type_mismatch');
        }
    }
}
