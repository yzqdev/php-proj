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

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property string $value
 * @property string $tag
 */
class Setting extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = ['key', 'value', 'tag'];

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = ['key', 'tag'];

    /**
     * {@inheritdoc}
     */
    protected $keyType = 'string';

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public static $encrypt;

    /**
     * 需要加密的数据字段
     *
     * @var array
     */
    public static $checkEncrypt = [
        'app_id',
        'app_secret',
        'api_key',
        'offiaccount_app_id',
        'offiaccount_app_secret',
        'miniprogram_app_id',
        'miniprogram_app_secret',
        'oplatform_app_id',
        'oplatform_app_secret',
        'qcloud_secret_id',
        'qcloud_secret_key',
        'qcloud_sms_app_id',
        'qcloud_sms_app_key',
        'qcloud_sms_template_id',
        'qcloud_sms_sign',
        'qcloud_captcha_app_id',
        'qcloud_captcha_secret_key',
        'qcloud_vod_url_key',
        'offiaccount_server_config_token',
        'uc_center_key',
    ];

    /**
     * 全局中的功能设置权限 -- 关联控制角色中的权限
     */
    public static $global_permission = [
        'site_create_thread0' => ['createThread.0'],
        'site_create_thread1' => ['createThread.1'],
        'site_create_thread2' => ['createThread.2'],
        'site_create_thread3' => ['createThread.3'],
        'site_create_thread4' => ['createThread.4'],
        'site_create_thread5' => ['createThread.5'],
        'site_create_thread6' => ['createThread.6'],
        'site_create_thread14' => ['createThread.14'], //提问
        'site_can_reward'  =>  ['switch.thread.canBeReward', 'thread.canBeReward']
    ];

    /**
     * Set the encrypt.
     *
     * @param $encrypt
     */
    public static function setEncrypt($encrypt)
    {
        self::$encrypt = $encrypt;
    }

    /**
     * each data decrypt
     */
    public function existDecrypt()
    {
        if (in_array($this->key, self::$checkEncrypt)) {
            return;
        }
    }

    /**
     * 解密数据
     *
     * @param $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        if (in_array($this->key, self::$checkEncrypt)) {
            $value = empty($value) ? $value : static::$encrypt->decrypt($value);
        }

        return $value;
    }

    /**
     * 加密数据
     * (insert 和 update 不操作 Eloquent)
     *
     * @param $key
     * @param $value
     */
    public static function setValue($key, &$value)
    {
        if (in_array($key, self::$checkEncrypt)) {
            $value = static::$encrypt->encrypt($value);
        }
    }

    public static function isMiniProgramVideoOn()
    {
        $request = app('request');
        $headers = $request->getHeaders();
        $server = $request->getServerParams();
        $headersStr = strtolower(json_encode($headers, 256));
        $serverStr = strtolower(json_encode($server, 256));

        if (strstr($serverStr, 'miniprogram') || strstr($headersStr, 'miniprogram') || 
            strstr($headersStr, 'compress')) {
            $settings = Setting::query()->where(['key' => 'miniprogram_video', 'tag' => 'wx_miniprogram'])->first();
            if(!$settings->value){
                return false;
            }
        }

        return true;
    }
}
