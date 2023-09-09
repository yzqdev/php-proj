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

namespace App\Api\Serializer;

use App\Models\User;
use App\Models\UserQq;
use App\Models\UserSignInFields;
use App\Repositories\UserFollowRepository;
use Discuz\Api\Serializer\AbstractSerializer;
use Discuz\Contracts\Setting\SettingsRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tobscure\JsonApi\Relationship;
use App\Models\Setting;
use App\Models\Ip;
use Illuminate\Http\Request;

class UserSerializer extends AbstractSerializer
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'users';

    /**
     * @var Gate
     */
    protected $gate;

    protected $userFollow;

    protected $Qqwry;
    protected $appcode;

    /**
     * @param Gate $gate
     * @param UserFollowRepository $userFollow
     */
    public function __construct(Gate $gate, UserFollowRepository $userFollow)
    {
        $this->gate = $gate;
        $this->userFollow = $userFollow;
        $settings = app(SettingsRepository::class);
        $this->Qqwry = new Ip($settings->get('site_appcode'));
        $this->appcode = $settings->get('site_appcode');
    }

    /**
     * {@inheritdoc}
     *
     * @param User $model
     */
    public function getDefaultAttributes($model)
    {
        $gate = $this->gate->forUser($this->actor);

        $canEdit = $gate->allows('edit', $model);
        // 获取当前网址url
        $url = Request::capture()->getSchemeAndHttpHost();
        $setting = Setting::query()->where('key', 'site_growups')->get();
        $growups = json_decode($setting[0]['value'], true);
        if($model->growup_count > $growups[7]['value']){
            $growup = $growups[7]['key'];
            $growupnema = $growups[7]['ivalue'];
        } elseif ($model->growup_count > $growups[6]['value']) {
            $growup = $growups[6]['key'];
            $growupnema = $growups[6]['ivalue'];
        } elseif ($model->growup_count > $growups[5]['value']) {
            $growup = $growups[5]['key'];
            $growupnema = $growups[5]['ivalue'];
        } elseif ($model->growup_count > $growups[4]['value']) {
            $growup = $growups[4]['key'];
            $growupnema = $growups[4]['ivalue'];
        } elseif ($model->growup_count > $growups[3]['value']) {
            $growup = $growups[3]['key'];
            $growupnema = $growups[3]['ivalue'];
        } elseif ($model->growup_count > $growups[2]['value']) {
            $growup = $growups[2]['key'];
            $growupnema = $growups[2]['ivalue'];
        } elseif ($model->growup_count > $growups[1]['value']) {
            $growup = $growups[1]['key'];
            $growupnema = $growups[1]['ivalue'];
        } elseif ($model->growup_count > $growups[0]['value']) {
            $growup = $growups[0]['key'];
            $growupnema = $growups[0]['ivalue'];
        } else {
            $growup = 0;
        }

        $attributes = [
            'id'                => (int) $model->id,
            'username'          => $model->username,
            'avatarUrl'         => $model->avatar,
            'isReal'            => $this->getIsReal($model),
            'integralCount'       => (int) $model->integral_count, // 积分 2022-10-1 张安冠
            'growup' => [
                'growupsCount'  => (int) $model->growup_count, // 成长值 2022-10-1 张安冠
                'growups'      => $growup,
                'growupnema'   => $growupnema,
                'growupimageUrl' =>"$url/images/growup/$growup.png",
                ],
            'threadCount'       => (int) $model->thread_count,
            'followCount'       => (int) $model->follow_count,
            'fansCount'         => (int) $model->fans_count,
            'likedCount'        => (int) $model->liked_count,
            'questionCount'     => (int) $model->question_count,
            'signature'         => $model->signature,
            'usernameBout'      => (int) $model->username_bout,
            'status'            => $model->status,
            'loginAt'           => $this->formatDate($model->login_at),
            'joinedAt'          => $this->formatDate($model->joined_at),
            'expiredAt'         => $this->formatDate($model->expired_at),
            'createdAt'         => $this->formatDate($model->created_at),
            'updatedAt'         => $this->formatDate($model->updated_at),
            'canEdit'           => $canEdit,
            'canDelete'         => $gate->allows('delete', $model),
            'showGroups'        => $gate->allows('showGroups', $model),     // 是否显示用户组
            'registerReason'    => $model->register_reason,                 // 注册原因
            'banReason'         => '',                                      // 禁用原因
            'denyStatus'        => (bool) $model->denyStatus,
        ];

        $whitelist = [
            '/api/follow/',
            '/api/users/'.Arr::get($this->getRequest()->getQueryParams(), 'id', '').'/',
            '/api/threads/'.Arr::get($this->getRequest()->getQueryParams(), 'id', '').'/'
        ];
        if (Str::contains($this->getRequest()->getUri()->getPath().'/', $whitelist)) {
            //需要时再查询关注状态 存在n+1
            $attributes['follow'] = $this->userFollow->findFollowDetail($this->actor->id, $model->id);
        }
        // 判断禁用原因
        if ($model->status == 1) {
            $attributes['banReason'] = !empty($model->latelyLog) ? $model->latelyLog->message : '' ;
        }

        // 限制字段 本人/权限 显示
        if ($canEdit || $this->actor->id === $model->id) {
            $attributes += [
                'originalMobile'    => $model->getRawOriginal('mobile'),
                'registerIp'        => $model->register_ip,
                'registerPort'      => $model->register_port,
                'lastLoginIp'       => $model->last_login_ip,
                'lastLoginPort'     => $model->last_login_port,
                'identity'          => $model->identity,
                'realname'          => $model->realname,
                'mobile'            => $model->mobile,
                'hasPassword'       => $model->password ? true : false,
            ];
            $ip = $model->register_ip;
            if (empty($ip)){
                $ip = $model->last_login_ip;
            }

            $info = $this->Qqwry->getlocation($ip);
            if (isset($this->appcode)){
                $attributes['province'] = $info;
            }
        }

        // 钱包余额
        if ($this->actor->id === $model->id) {
            $attributes += [
                'canWalletPay'  => $gate->allows('walletPay', $model),
                'walletBalance' => $this->actor->userWallet->available_amount,
                'walletFreeze'  => $this->actor->userWallet->freeze_amount,
            ];
        }

        // 是否管理员
        if ($this->actor->isAdmin()) {
            $attributes += [
                'canEditUsername' => true,  // 可否更改用户名
            ];
        } else {
            /** @var SettingsRepository $settings */
            $settings = app(SettingsRepository::class);

            $attributes += [
                'canEditUsername' => $model->username_bout < $settings->get('username_bout', 'default', 1),
            ];
        }
        if($model->bind_type == 2) {
            $attributes['avatarUrl'] = ! empty($attributes['avatarUrl']) ? $attributes['avatarUrl'] : $this->qqAvatar($model);
        }

        return $attributes;
    }

    /**
     * 是否实名认证
     *
     * @param User $model
     * @return string
     */
    public function getIsReal(User $model)
    {
        if (isset($model->realname) && $model->realname != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $user
     * @return Relationship
     */
    public function wechat($user)
    {
        return $this->hasOne($user, UserWechatSerializer::class);
    }


    /**
     * @param $user
     * @return Relationship
     */
    public function groups($user)
    {
        return $this->hasMany($user, GroupSerializer::class);
    }

    public function extFields($user)
    {
        return $this->hasMany($user, UserSignInSerializer::class);
    }
    /**
     * @param $user
     * @return Relationship
     */
    public function deny($user)
    {
        return $this->hasMany($user, UserSerializer::class);
    }

    /**
     * @param $user
     * @return Relationship
     */
    public function dialog($user)
    {
        return $this->hasOne($user, DialogSerializer::class);
    }

    /**
     * qq头像
     * @param User $user
     * @return string
     */
    public function qqAvatar(User $user)
    {
        $qqUser = UserQq::where('user_id', $user->id)->first();
        if(! $qqUser) {
            return '';
        }
        return $qqUser->headimgurl;
    }
}
