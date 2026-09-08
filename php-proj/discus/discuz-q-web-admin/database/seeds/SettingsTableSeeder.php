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

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = new Setting();
        $settings->truncate();
        $settings->insert([
            [
                'key' => 'site_close',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '0',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_mode',           // 站点模式：public 公开，pay 付费
                'value' => 'public',            // 默认公开
                'tag' => 'default',
            ],
            [
                'key' => 'site_author',         // 站长用户ID 1 管理员
                'value' => '1',                 // 默认用户1
                'tag' => 'default',
            ],
            [
                'key' => 'site_master_scale',   // 站长分成比例
                'value' => '0',                 // 默认 0
                'tag' => 'default',
            ],
            [
                'key' => 'site_author_scale',   // 用户分成比例
                'value' => '10',                // 默认 10
                'tag' => 'default',
            ],
            [
                'key' => 'register_close',      // 注册开关：0 禁止，1 允许
                'value' => '1',                 // 默认允许
                'tag' => 'default',
            ],
            [
                'key' => 'register_validate',   // 注册审核：0关闭，1开启
                'value' => '0',                 // 默认关闭
                'tag' => 'default',
            ],
            [
                'key' => 'qcloud_sms',          // 腾讯云短信开关：0 关闭，1 开启
                'value' => '0',                 // 默认关闭
                'tag' => 'qcloud',
            ],
            [
                'key' => 'qcloud_vod',          // 腾讯云点播开关：0 关闭，1 开启
                'value' => '0',                 // 默认关闭
                'tag' => 'qcloud',
            ],
            [
                'key' => 'support_img_ext',     // 默认支持的图片扩展名
                'value' => 'png,gif,jpg,jpeg,heic',
                'tag' => 'default',
            ],
            [
                'key' => 'support_file_ext',    // 默认支持的附件扩展名
                'value' => 'doc,docx,pdf,zip',
                'tag' => 'default',
            ],
            [
                'key' => 'support_max_size',    // 默认支持附件最大大小 MB单位
                'value' => 5,
                'tag' => 'default',
            ],
            [
                'key' => 'miniprogram_video',   // 小程序视频开关：0 关闭，1 开启
                'value' => 0,                   // 默认开启
                'tag' => 'wx_miniprogram',
            ],
            [
                'key' => 'site_open_sort',    // 是否开启智能排序，0不开启，1开启
                'value' => 0,
                'tag' => 'default',
            ],
            [
                'key' => 'site_create_thread0',    // 允许发布文字帖(普通)，0为不允许，1为允许，以下一样
                'value' => 1,
                'tag' => 'default',
            ],
            [
                'key' => 'site_create_thread1',    // 允许发布帖子(长文)
                'value' => 1,
                'tag' => 'default',
            ],
            [
                'key' => 'site_create_thread2',    // 允许发布视频帖
                'value' => 1,
                'tag' => 'default',
            ],
            [
                'key' => 'site_create_thread3',    //允许发布图片帖
                'value' => 1,
                'tag' => 'default',
            ],
            [
                'key' => 'site_create_thread4',    // 允许发布语音帖
                'value' => 1,
                'tag' => 'default',
            ],
            [
                'key' => 'site_create_thread5',    // 允许发布问答帖(悬赏帖)
                'value' => 0,
                'tag' => 'default',
            ],
            [
                'key' => 'site_create_thread6',    // 允许发布商品帖
                'value' => 1,
                'tag' => 'default',
            ],
            [
                'key' => 'site_ask',    // 允许发布商品帖
                'value' => '[{"value":1,"shuoming":"赞助开发者，了解开发动态，与开发者 0 距离 沟通","zanzhu":" ( 每人一年限制赞助 50元  )","fuwu":"✓ 安装咨询  ✓ 使用咨询  ✓ 更新系统  ✓ 了解动态","hangshu":2,"tishi":"提交: 问题, 需求, 缺点"}]',
                'tag' => 'default',
            ],
            [
                'key' => 'site_manage',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'api_freq',    // 允许发布商品帖
                'value' => '{"get":{"freq":500,"forbidden":20},"post":{"freq":200,"forbidden":30}}',
                'tag' => 'default',
            ],
            [
                'key' => 'site_integral',    // 积分
                'value' => '[{"key":1,"desc":"登录积分设置","name":"登录积分设置","ivalue":1,"name2":"每天登录积分设置","value":0},{"key":2,"desc":"注册","name":"注册","ivalue":10},{"key":3,"desc":"评论积分","name":"评论积分","value":1,"name2":"每天奖励次数","ivalue":0},{"key":4,"name":"点赞积分","ivalue":1,"name2":"每天奖励次数","value":0,"name3":"取消点赞积分","unvalue":1,"name4":"每天扣除取消点赞积分","unvalueday":0},{"key":5,"name":"收藏积分","ivalue":1,"name2":"每天奖励次数","value":0,"name3":"取消收藏积分","unvalue":1,"name4":"每天扣除取消收藏积分","unvalueday":0},{"key":6,"name":"关注积分","ivalue":1,"name2":"每天关注奖励次数","value":0,"name3":"取消关注积分","unvalue":1,"name4":"每天扣除取消关注积分","unvalueday":0},{"key":7,"name":"发布帖子积分","ivalue":1,"name2":"每天发布帖子奖励次数","value":0,"name3":"取消发布帖子积分","unvalue":1,"name4":"每天扣除取消发布帖子积分","unvalueday":0},{"key":8,"name":"发布文章积分","ivalue":1,"name2":"每天发布文章奖励次数","value":0,"name3":"取消发布文章积分","unvalue":1,"name4":"每天扣除取消发布文章积分","unvalueday":0},{"key":9,"name":"发布图片积分","ivalue":1,"name2":"每天发布图片奖励次数","value":0,"name3":"取消发布图片积分","unvalue":1,"name4":"每天扣除取消发布图片积分","unvalueday":0},{"key":10,"name":"发布视频积分","ivalue":1,"name2":"每天发布视频奖励次数","value":0,"name3":"取消发布视频积分","unvalue":1,"name4":"每天扣除取消发布视频积分","unvalueday":0},{"key":11,"name":"发布视频积分","ivalue":1,"name2":"每天发布视频奖励次数","value":0,"name3":"取消发布视频积分","unvalue":1,"name4":"每天扣除取消发布视频积分","unvalueday":0},{"key":12,"name":"发布问答积分","ivalue":1,"name2":"每天发布问答奖励次数","value":0,"name3":"取消发布问答积分","unvalue":1,"name4":"每天扣除取消发布问答积分","unvalueday":0},{"key":13,"name":"分享积分","ivalue":1,"name2":"每天分享奖励次数","value":0},{"key":14,"name":"阅读积分","ivalue":1,"name2":"每天阅读奖励次数","value":0},{"key":15,"name":"精华积分","ivalue":1,"name2":"每天精华奖励次数","value":0},{"key":16,"name":"发布圈贴积分","ivalue":1,"name2":"每天发布圈贴奖励次数","value":0,"name3":"取消发布圈贴积分","unvalue":1,"name4":"每天扣除取消发布圈贴积分","unvalueday":0},{"key":17,"name":"发布课贴积分","ivalue":1,"name2":"每天发布课贴奖励次数","value":0,"name3":"取消发布课贴积分","unvalue":1,"name4":"每天扣除取消发布课贴积分","unvalueday":0}]',
                'tag' => 'default',
            ],
            [
                'key' => 'site_growup',    // 成长
                'value' => '[{"key":1,"desc":"登录成长值设置","name":"登录成长值设置","ivalue":10,"name2":"每天登录成长值设置","value":0},{"key":2,"desc":"注册","name":"注册","ivalue":10},{"key":3,"desc":"评论成长值","name":"评论成长值","value":1,"name2":"每天奖励次数","ivalue":0},{"key":4,"name":"点赞成长值","ivalue":1,"name2":"每天奖励次数","value":0,"name3":"取消点赞成长值","unvalue":1,"name4":"每天扣除取消点赞成长值","unvalueday":0},{"key":5,"name":"收藏成长值","ivalue":1,"name2":"每天奖励次数","value":0,"name3":"取消收藏成长值","unvalue":1,"name4":"每天扣除取消收藏成长值","unvalueday":0},{"key":6,"name":"关注成长值","ivalue":1,"name2":"每天关注奖励次数","value":0,"name3":"取消关注成长值","unvalue":1,"name4":"每天扣除取消关注成长值","unvalueday":0},{"key":7,"name":"发布帖子成长值","ivalue":1,"name2":"每天发布帖子奖励次数","value":0,"name3":"取消发布帖子成长值","unvalue":1,"name4":"每天扣除取消发布帖子成长值","unvalueday":0},{"key":8,"name":"发布文章成长值","ivalue":1,"name2":"每天发布文章奖励次数","value":0,"name3":"取消发布文章成长值","unvalue":1,"name4":"每天扣除取消发布文章成长值","unvalueday":0},{"key":9,"name":"发布图片成长值","ivalue":1,"name2":"每天发布图片奖励次数","value":0,"name3":"取消发布图片成长值","unvalue":1,"name4":"每天扣除取消发布图片成长值","unvalueday":0},{"key":10,"name":"发布视频成长值","ivalue":1,"name2":"每天发布视频奖励次数","value":0,"name3":"取消发布视频成长值","unvalue":1,"name4":"每天扣除取消发布视频成长值","unvalueday":0},{"key":11,"name":"发布视频成长值","ivalue":1,"name2":"每天发布视频奖励次数","value":0,"name3":"取消发布视频成长值","unvalue":1,"name4":"每天扣除取消发布视频成长值","unvalueday":0},{"key":12,"name":"发布问答成长值","ivalue":1,"name2":"每天发布问答奖励次数","value":0,"name3":"取消发布问答成长值","unvalue":1,"name4":"每天扣除取消发布问答成长值","unvalueday":0},{"key":13,"name":"分享成长值","ivalue":1,"name2":"每天分享奖励次数","value":0},{"key":14,"name":"阅读成长值","ivalue":1,"name2":"每天阅读奖励次数","value":0},{"key":15,"name":"精华成长值","ivalue":1,"name2":"每天精华奖励次数","value":0},{"key":16,"name":"发布圈贴成长值","ivalue":1,"name2":"每天发布圈贴奖励次数","value":0,"name3":"取消发布圈贴成长值","unvalue":1,"name4":"每天扣除取消发布圈贴成长值","unvalueday":0},{"key":17,"name":"发布课贴成长值","ivalue":1,"name2":"每天发布课贴奖励次数","value":0,"name3":"取消发布课贴成长值","unvalue":2,"name4":"每天扣除取消发布课贴成长值","unvalueday":0}]',
                'tag' => 'default',
            ],
            [
                'key' => 'site_growups',    // 成长设置
                'value' => '[{"key":1,"desc":"成长值设置","name":"成长值身份一","ivalue":"新手","name2":"需要成长值","value":0,"imageUrl":""},{"key":2,"desc":"成长值设置","name":"成长值身份二","ivalue":"秀才","name2":"需要成长值","value":10,"imageUrl":""},{"key":3,"desc":"成长值设置","name":"成长值身份三","ivalue":"举人","name2":"需要成长值","value":50,"imageUrl":""},{"key":4,"desc":"成长值设置","name":"成长值身份四","ivalue":"贡士","name2":"需要成长值","value":200,"imageUrl":""},{"key":5,"desc":"成长值设置","name":"成长值身份五","ivalue":"进士","name2":"需要成长值","value":400,"imageUrl":""},{"key":6,"desc":"成长值设置","name":"成长值身份五","ivalue":"状元","name2":"需要成长值","value":600,"imageUrl":""},{"key":7,"desc":"成长值设置","name":"成长值身份五","ivalue":"榜眼","name2":"需要成长值","value":1000,"imageUrl":""},{"key":8,"desc":"成长值设置","name":"成长值身份五","ivalue":"探花","name2":"需要成长值","value":5000,"imageUrl":""}]',
                'tag' => 'default',
            ],
            [
                'key' => 'site_signin',    // 签到
                'value' => '[{"key":1,"desc":"签到奖励","name":"签到奖励","ivalue":2},{"key":2,"desc":"每天签到次数","name":"每天签到次数","second":1,"name2":"X天内可以补签","repair":10},{"key":3,"desc":"补签需要消耗","name":"补签需要消耗","ivalue":3,"name2":"补签需要消耗多少值","repair":30}]',
                'tag' => 'default',
            ],
            [
                'key' => 'site_signinday',    // 允许发布商品帖
                'value' => '[{"key":1,"desc":"签到","name":"第一天签到奖励","value":1},{"key":2,"desc":"签到","name":"第二天签到奖励","value":2},{"key":3,"desc":"签到","name":"第三天签到奖励","value":3},{"key":4,"desc":"签到","name":"第四天签到奖励","value":4},{"key":5,"desc":"签到","name":"第五天签到奖励","value":5},{"key":6,"desc":"签到","name":"第六天签到奖励","value":6},{"key":7,"desc":"签到","name":"第七天签到奖励","value":7},{"key":8,"desc":"签到","name":"第N天签到奖励","value":85}]',
                'tag' => 'default',
            ],
            [
                'key' => 'img_hangshu', // imge-h5图imge.png
                'value' => 5,
                'tag' => 'default',
            ],
            [
                'key' => 'img_wei_hangshu', // imge-小程序图imge.png
                'value' => 5,
                'tag' => 'default',
            ],
            [
                'key' => 'site_htmlapp', // imge-h5图imge.png
                'value' => '[{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标1","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址1"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标2","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标3","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标4","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标5","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标6","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标7","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标8","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标9","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标10","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"}]',
                'tag' => 'default',
            ],
            [
                'key' => 'site_weiapp', // imge-小程序图imge.png
                'value' => '[{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标1","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址1"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标2","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标3","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标4","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标5","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标6","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标7","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标8","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标9","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"},{"img":{"imageUrl": "","imgWidht": 0,"imgHeight": 0,"text": "图标10","textrule": "尺寸：80px*88px"},"url":"https:\/\/","value":1,"name":"网址"}]',
                'tag' => 'default',
            ]
        ]);
    }
}
