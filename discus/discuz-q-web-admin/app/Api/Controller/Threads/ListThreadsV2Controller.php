<?php
/**
 * Copyright (C) 2021 Tencent Cloud.
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
 * 张安冠 2020-10-02
 */

namespace App\Api\Controller\Threads;

use App\Api\Serializer\AttachmentSerializer;
use App\Common\CacheKey;
use App\Common\ResponseCode;
use App\Common\Utils;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\GroupUser;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Posts;
use App\Models\PostGoods;
use App\Models\PostUser;
use App\Models\Question;
use App\Models\Sequence;
use App\Models\Thread;
use App\Models\ThreadReward;
use App\Models\ThreadVideo;
use App\Models\User;
use App\Models\Setting;
use Discuz\Base\DzqController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
//use App\Commands\Tools\Tools;

class ListThreadsV2Controller extends DzqController
{
    public function main()
    {
        $filter = $this->inPut('filter');
        $page = $this->inPut('page');
        $perPage = $this->inPut('perPage');
        $homeSequence = $this->inPut('homeSequence');//默认首页
        $quan = $this->inPut('quan');//圈子ID
        $ke = $this->inPut('ke');//课程ID
        $serializer = $this->app->make(AttachmentSerializer::class);
        $groups = $this->user->groups->toArray();
        $groupIds = array_column($groups, 'id');
        $permissions = Permission::categoryPermissions($groupIds);
        $threads = $this->getOriginThreads($page, $filter, $perPage, $homeSequence, $quan, $ke);
        $threadList = $threads['pageData'] ?? [];
        !$threads && $threadList = [];
        $userIds = array_unique(array_column($threadList, 'user_id'));
        $groups = GroupUser::instance()->getGroupInfo($userIds);
        $groups = array_column($groups, null, 'user_id');
        $users = User::instance()->getUsers($userIds);
        $users = array_column($users, null, 'id');
        $threadIds = array_column($threadList, 'id');
        $posts = Post::instance()->getPosts($threadIds);
        $postsByThreadId = array_column($posts, null, 'thread_id');
        $postIds = array_column($posts, 'id');
        $likedPostIds = PostUser::instance()->getPostIdsByUid($postIds, $this->user->id);
        $attachments = Attachment::instance()->getAttachments($postIds, [Attachment::TYPE_OF_FILE, Attachment::TYPE_OF_IMAGE]);
        $attachmentsByPostId = Utils::pluckArray($attachments, 'type_id');
        $threadRewards = ThreadReward::instance()->getRewards($threadIds);
        $paidThreadIds = $this->getPayArr($threadIds, Order::ORDER_TYPE_ATTACHMENT);
        $pay = $this->getPayArr($threadIds, Order::ORDER_TYPE_THREAD);

        $result = [];
        $linkString = '';
        foreach ($threadList as $thread) {
            $userId = $thread['user_id'];
            $user = [
                'pid' => -1,
                'userName' => '匿名用户',
            ];
            if ((!$thread['is_anonymous'] && !empty($users[$userId])) || $this->user->id == $thread['user_id']) {
                $user = $this->getUserInfo($users[$userId]);
            }
            $group = [];
            if (!empty($groups[$userId])) {
                $group = $this->getGroupInfo($groups[$userId]);
            }

            $attachments = [];
            $attachmentsfree = [];
            $post = null;
            if (!empty($postsByThreadId[$thread['id']])) {
                !empty($postsByThreadId[$thread['id']]) && $post = $postsByThreadId[$thread['id']];
                if (!empty($post['id']) && !empty($attachmentsByPostId[$post['id']])) {
                    $attachments = $attachmentsByPostId[$post['id']];
                }
            }
            $attachment = $this->filterAttachment($thread, $paidThreadIds, $pay, $attachments, $serializer);
            $attachmentfree = $this->filterAttachment($thread, $paidThreadIds, $pay, $attachments, $serializer);
            $thread = $this->getThread($thread, $post, $likedPostIds, $permissions, $pay);

            $linkString .= $thread['summary'];
            $rewards = null;
            if (isset($threadRewards[$thread['pid']])) {
                $rewards = $threadRewards[$thread['pid']];
            }
            $result[] = [
                'user' => $user,
                'group' => $group,
                'rewards' => $rewards,
                'thread' => $thread,
                'attachment' => $attachment,
                'attachmentfree' => $attachmentfree,
            ];
        }
        list($search, $replace) = Thread::instance()->getReplaceString($linkString);
        foreach ($result as &$item) {
            $thread = $item['thread'];
            $item['thread']['summary'] = str_replace($search, $replace, $thread['summary']);
        }
        foreach ($result as &$item) {
            $thread = $item['thread'];
            $item['thread']['description'] = str_replace($search, $replace, $thread['description']);
        }
        $threads['pageData'] = $result;
        $this->outPut(ResponseCode::SUCCESS, '', $threads);
    }

    private function getFilterThreadsList($page, $filter, $perPage, $homeSequence, $quan)
    {
        $cache = app('cache');
        $key = md5(json_encode($filter) . $perPage . $homeSequence);
        if ($page == 1) {
            $threads = $this->getCache($cache, $key);
//            $threads = false;
            if (!$threads) {
                $threads = $this->getOriginThreads($page, $filter, $perPage, $homeSequence, $quan, $ke);
                $this->putCache($cache, $key, $threads);
                return $threads;
            } else {
                return $threads;
            }
        } else {
            $threads = $this->getOriginThreads($page, $filter, $perPage, $homeSequence, $quan, $ke);
        }
        return $threads;
    }

    private function getOriginThreads($page, $filter, $perPage, $homeSequence, $quan, $ke)
    {
        if ($homeSequence) {
            $threads = $this->getDefaultHomeThreads($filter, $page, $perPage, $quan, $ke);
        } else {
            $threads = $this->getFilterThreads($filter, $page, $perPage, $quan, $ke);
        }
        return $threads;
    }

    private function getPayArr($threadIds, $type)
    {
        $data = [];
        $getOrder = Order::query()->whereIn('thread_id', $threadIds)
            ->where('user_id', $this->user->id)
            ->where('status', Order::ORDER_STATUS_PAID)
            ->get()->toArray();

        foreach ($getOrder as $key => $val) {
            if ($val['type'] == $type) {
                $data[] = $val['thread_id'];
            }
        }
        return $data;
    }

    private function canViewThread($thread, $paidThreadIds)
    {
        return $this->user->id == $thread['user_id'] || $this->user->isAdmin() || in_array($thread['id'], $paidThreadIds);
    }

    /**
     * @desc 筛选在列表是否展示图片附件
     * @param $thread
     * @param $paidThreadIds
     * @param $pay
     * @param $attachments
     * @param $serializer
     * @return array
     */
    private function filterAttachment($thread, $paidThreadIds, $pay, $attachments, $serializer)
    {
        $attachment = [];
        if ($this->canViewThread($thread, $paidThreadIds) || $this->canViewThread($thread, $pay)) {
            $attachment = $this->getAttachment($attachments, $thread, $serializer);
        } else {
            if ($thread['price'] == 0) {
                $attachment = $this->getAttachment($attachments, $thread, $serializer);
            }

            //附件收费
            if ($thread['attachment_price'] > 0 || ($thread['type'] == Thread::TYPE_OF_IMAGE && $thread['price'] > 0)) {
                $attachment = $this->getAttachment($attachments, $thread, $serializer);
                $attachment = array_filter($attachment, function ($item) {
                    $fileType = strtolower($item['fileType']);
                    return strstr($fileType, 'image');
                });
            }
        }
        return $attachment;
    }


    private function getThread($thread, $firstPost, $likedPostIds, $permissions, $pay)
    {
        $description = Post::query()->where('thread_id', $thread['id'])->first();
        $description = $description['content'];
//        $pattern='/\.jpg|\.png|\#|\.txt/';
//        $description = preg_replace($pattern,"",$description); //过滤applet标签
        $description = htmlspecialchars_decode($description);//把一些预定义的 HTML 实体转换为字符
        $description = strip_tags($description);
        $description = str_replace(array("/r/n/", "*", "\n"), "", $description);
        $description = Str::limit($description, 150); // 获取文字简介 2022-10-10 张安冠
        //$Tools = new Tools(); //获取地址  11.19 Nahida
        //$arr = $Tools->RecontentHtml($description,$description);

        $data = [
            'pid' => $thread['id'],
            'paid' => $this->canViewThread($thread, $pay),
            'type' => $thread['type'],
            'categoryId' => $thread['category_id'],
            'title' => $thread['title'],
            'summary' => '',
            'description' => $description,
            'price' => $thread['price'],
            'attachmentPrice' => $thread['attachment_price'],
            'postCount' => $thread['post_count'] - 1,
            'viewCount' => $thread['view_count'],
            'rewardedCount' => $thread['rewarded_count'],
            'paidCount' => $thread['paid_count'],
            'longitude' => $thread['longitude'],
            'latitude' => $thread['latitude'],
            'address' => $thread['address'],
            'location' => $thread['location'],
            'isPoster' => $thread['is_poster'], // 海报
            'isPinglun' => $thread['is_pinglun'], // 关闭评论
			'isOriginal' => $thread['is_original'], // 原创
            'isEssence' => $thread['is_essence'],
            'createdAt' => date('Y-m-d H:i:s', strtotime($thread['created_at'])),
            'diffCreatedAt' => Utils::diffTime($thread['created_at']),
            'isRedPacket' => $thread['is_red_packet'],
            'canViewPost' => $this->canViewPosts($thread, $permissions),
            'canLike' => true,
            'isLiked' => false,
            'isDraft' => $thread['is_draft'],
            'likedCount' => 0,
            'firstPostId' => null,
            'replyCount' => 0,
            'extension' => null,
            'vurl'              => $arr["vurl"],
            'bvid'              => $arr["bvid"],
            'wavurl'            => $arr["wavurl"],
        ];
        //点赞相关属性
        if (!empty($firstPost)) {
            $data['canLike'] = $this->canLikeThread($permissions);
            $data['isLiked'] = in_array($firstPost['id'], $likedPostIds) ? true : false;
            $data['likedCount'] = $firstPost['like_count'];
            $data['firstPostId'] = $firstPost['id'];
            $data['replyCount'] = $firstPost['reply_count'];
        }
        switch ($thread['type']) {
            case Thread::TYPE_OF_IMAGE:
            case Thread::TYPE_OF_TEXT:
                $data['title'] = Post::instance()->getContentSummary($firstPost);
                break;
            case Thread::TYPE_OF_AUDIO:
            case Thread::TYPE_OF_VIDEO:
                $data['title'] = Post::instance()->getContentSummary($firstPost);
                $data['extension'] = [
                    Thread::EXT_VIDEO => ThreadVideo::instance()->getThreadVideo($thread['id'])
                ];
                break;
            case Thread::TYPE_OF_GOODS:
                $postId = true;
                $data['title'] = Post::instance()->getContentSummary($firstPost, $postId);;
                $data['extension'] = [
                    Thread::EXT_GOODS => PostGoods::instance()->getPostGoods($postId)
                ];
                break;
            case Thread::TYPE_OF_QUESTION:
                $data['title'] = Post::instance()->getContentSummary($firstPost);
                $data['extension'] = [
                    Thread::EXT_QA => Question::instance()->getQuestions($thread['id'])
                ];
                break;
            default:
                break;
        }
        $data['description'] = Post::instance()->getContentSummary($firstPost);
        $data['summary'] = $data['title'];
        return $data;
    }

    private function canViewPosts($thread, $permissions)
    {
        if ($this->user->isAdmin() || $this->user->id == $thread['user_id']) {
            return true;
        }
        $viewPostStr = 'category' . $thread['category_id'] . '.thread.viewPosts';
        $viewaskPostStr = 'category' . $thread['category_id'] . '.thread.viewaskPosts';
        if (in_array('thread.viewPosts', $permissions) || in_array($viewPostStr, $permissions) || in_array($viewaskPostStr, $permissions)) {
            return true;
        }
        return false;
    }

    private function canLikeThread($permissions)
    {
        if ($this->user->isAdmin()) {
            return true;
        }
        $permission = 'thread.likePosts';
        return in_array($permission, $permissions);
    }

    private function getAttachment($attachments, $thread, $serializer)
    {
        $result = [];
        foreach ($attachments as $attachment) {
//            $result[] = $this->camelData($serializer->getDefaultAttributes($attachment, $this->user));
            $result[] = $this->camelData($serializer->getBeautyAttachment($attachment, $thread, $this->user));
        }
        return $result;
    }

    private function getGroupInfo($group)
    {
        return [
            'pid' => $group['group_id'],
            'groupName' => $group['groups']['name'],
            'groupIcon' => $group['groups']['icon'],
            'isDisplay' => $group['groups']['is_display']
        ];
    }

    // 发帖用户资料 2022-10-3 （张安冠）
    private function getUserInfo($user)
    {
        // 获取当前网址url
        $url = Request::capture()->getSchemeAndHttpHost();
        $setting = Setting::query()->where('key', 'site_growups')->get();
        $growups = json_decode($setting[0]['value'], true);
        if($user['growup_count'] > $growups[7]['value']){
            $growup = $growups[7]['key'];
            $growupnema = $growups[7]['ivalue'];
        } elseif ($user['growup_count'] > $growups[6]['value']) {
            $growup = $growups[6]['key'];
            $growupnema = $growups[6]['ivalue'];
        } elseif ($user['growup_count'] > $growups[5]['value']) {
            $growup = $growups[5]['key'];
            $growupnema = $growups[5]['ivalue'];
        } elseif ($user['growup_count'] > $growups[4]['value']) {
            $growup = $growups[4]['key'];
            $growupnema = $growups[4]['ivalue'];
        } elseif ($user['growup_count'] > $growups[3]['value']) {
            $growup = $growups[3]['key'];
            $growupnema = $growups[3]['ivalue'];
        } elseif ($user['growup_count'] > $growups[2]['value']) {
            $growup = $growups[2]['key'];
            $growupnema = $growups[2]['ivalue'];
        } elseif ($user['growup_count'] > $growups[1]['value']) {
            $growup = $growups[1]['key'];
            $growupnema = $growups[1]['ivalue'];
        } elseif ($user['growup_count'] > $growups[0]['value']) {
            $growup = $growups[0]['key'];
            $growupnema = $growups[0]['ivalue'];
        } else {
            $growup = 0;
        }
        return [
            'pid' => $user['id'],
            'userName' => $user['username'],
            'avatar' => $user['avatar'],
            'threadCount' => $user['thread_count'],
            'followCount' => $user['follow_count'],
            'growup' => [
                'growupsCount' => $user['growup_count'],
                'growups' => $growup,
                'growupnema' => $growupnema,
                'growupimageUrl' =>"$url/images/growup/$growup.png",
                ],
            'integralCount' => $user['integral_count'],
            'fansCount' => $user['fans_count'],
            'likedCount' => $user['liked_count'],
            'questionCount' => $user['question_count'],
            'isRealName' => !empty($user['realname']),
            'joinedAt' => date('Y-m-d H:i:s', strtotime($user['joined_at']))
        ];
    }

    /**
     * @desc 获取默认排序首页数据
     * @param $filter
     * @param $currentPage
     * @param $perPage
     * @return array|bool
     */
    private function getDefaultHomeThreads($filter, $currentPage, $perPage, $quan, $ke)
    {
        $sequence = Sequence::query()->first();
        if (empty($sequence)) return false;
        $categoryIds = [];
        !empty($sequence['category_ids']) && $categoryIds = explode(',', $sequence['category_ids']);
        $categoryIds = Category::instance()->getValidCategoryIds($this->user, $categoryIds);
        if (!$categoryIds) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '没有浏览权限00000');
        }

        if (empty($filter)) $filter = [];
        isset($filter['types']) && $types = $filter['types'];
        isset($filter['sort']) && $sort = $filter['sort'];

        !empty($sequence['group_ids']) && $groupIds = explode(',', $sequence['group_ids']);
        !empty($sequence['user_ids']) && $userIds = explode(',', $sequence['user_ids']);
        !empty($sequence['topic_ids']) && $topicIds = explode(',', $sequence['topic_ids']);
        !empty($sequence['thread_ids']) && $threadIds = explode(',', $sequence['thread_ids']);
        !empty($sequence['block_user_ids']) && $blockUserIds = explode(',', $sequence['block_user_ids']);
        !empty($sequence['block_topic_ids']) && $blockTopicIds = explode(',', $sequence['block_topic_ids']);
        !empty($sequence['block_thread_ids']) && $blockThreadIds = explode(',', $sequence['block_thread_ids']);
        //isset($filter['sort']) && $sort = $filter['sort'];
        $threads = Thread::query()
            ->from('threads as th1')
            ->whereNull('th1.deleted_at')
            ->where('th1.is_approved', Thread::APPROVED)
            ->where('th1.is_draft', Thread::IS_NOT_DRAFT);

        $isMiniProgramVideoOn = Setting::isMiniProgramVideoOn();
        if (!$isMiniProgramVideoOn) {
            $threads = $threads->where('th1.type', '<>', Thread::TYPE_OF_VIDEO);
        }
        if (!empty($types)) {
            $threads = $threads->whereIn('type', $types);
        } else {
            $types = [0,1,2,3,4,5,6];
            $threads = $threads->whereIn('type', $types);
        }
        // 202-10-10-增加默认排列 张安冠
        if (!empty($sort)) {
            if ($sort == Thread::SORT_BY_THREAD) {//按照发帖时间排序
               $threads = $threads->orderByDesc('created_at');
            } else if ($sort == Thread::SORT_BY_CREAT) {//按照发布时间排序
                //添加评论字段CREATed_at
                $threads =$threads->orderByDesc('created_at');
            } else if ($sort == Thread::SORT_BY_POST) {//按照评论时间排序
                //添加评论字段posted_at
                $threads =$threads->where('posted_at', '<>', '')->orderByDesc('posted_at');
            }
        }

        // 圈子ID
        if ($quan > 0) {
            $threads = $threads->where('quanid', $quan);
        } else {
            $threads = $threads->where('quanid', NULL);
        }

        // 课程ID
        if ($ke > 0) {
            $threads = $threads->where('keid', $ke);
        } else {
            $threads = $threads->where('keid', NULL);
        }

        if (!empty($categoryIds)) {
            $threads = $threads->whereIn('th1.category_id', $categoryIds);
        }
        if (!empty($groupIds)) {
            $threads = $threads
                ->leftJoin('group_user as g1', 'g1.user_id', '=', 'th1.user_id')
                ->whereIn('g1.group_id', $groupIds);
        }
        if (!empty($topicIds)) {
            $threads = $threads
                ->leftJoin('thread_topic as tp1', 'tp1.thread_id', '=', 'th1.id')
                ->whereIn('tp1.topic_id', $topicIds);
        }
        if (!empty($userIds)) {
            $threads = $threads->whereIn('th1.user_id', $userIds);
        }
        if (!empty($threadIds)) {
            $threads = $threads->whereIn('th1.id', $threadIds);
        }

        if (!empty($blockUserIds)) {
            $threads->whereNotExists(function ($query) use ($blockUserIds) {
                $query->whereIn('th1.user_id', $blockUserIds);
            });
        }
        if (!empty($blockThreadIds)) {
            $threads->whereNotExists(function ($query) use ($blockThreadIds) {
                $query->whereIn('th1.id', $blockThreadIds);
            });
        }
        if (!empty($blockTopicIds)) {
            $threads->whereNotExists(function ($query) use ($blockTopicIds) {
                $query->whereIn('tp1.topic_id', $blockTopicIds);
            });
        }
        $threads = $threads->orderByDesc('th1.created_at');
        return $this->pagination($currentPage, $perPage, $threads);
    }

    private function getFilterThreads($filter, $currentPage, $perPage, $quan)
    {
        if (empty($filter)) $filter = [];
        $this->dzqValidate($filter, [
            'sticky' => 'integer|in:0,1',
            'essence' => 'integer|in:0,1',
            'types' => 'array',
            'categoryids' => 'array',
            'sort' => 'integer|in:1,2,3',
            'quan' => 'integer|in:1',
            'attention' => 'integer|in:0,1',
        ]);
        $stick = 0;
        $essence = null;
        $types = [];
        $categoryids = [];
        $sort = 1;
        $attention = 0;
        isset($filter['sticky']) && $stick = $filter['sticky'];
        isset($filter['essence']) && $essence = $filter['essence'];
        isset($filter['types']) && $types = $filter['types'];
        isset($filter['categoryids']) && $categoryids = $filter['categoryids'];
        isset($filter['sort']) && $sort = $filter['sort'];
        isset($filter['attention']) && $attention = $filter['attention'];

        $categoryids = Category::instance()->getValidCategoryIds($this->user, $categoryids);
        if (!$categoryids) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '没有浏览权限');
        }
        //评论排序
        $threads = Thread::query()
            ->whereNull('threads.deleted_at')
            ->where('is_sticky', $stick)
            ->where('is_draft', Thread::IS_NOT_DRAFT)
            ->where('is_approved', Thread::APPROVED);
        !empty($essence) && $threads = $threads->where('is_essence', $essence);

        $isMiniProgramVideoOn = Setting::isMiniProgramVideoOn();
        if (!$isMiniProgramVideoOn) {
            $threads = $threads->where('threads.type', '<>', Thread::TYPE_OF_VIDEO);
        }


        // SORT_BY_CREAT=2

        if ($sort == Thread::SORT_BY_THREAD) {//按照发帖时间排序
            $threads->orderByDesc('threads.updated_at'); // 修改时间排列
        } else if ($sort == Thread::SORT_BY_CREAT) {//按照发布时间排序
            //添加评论字段CREATed_at
            $threads->orderByDesc('threads.created_at');
        } else if ($sort == Thread::SORT_BY_POST) {//按照评论时间排序
            //添加评论字段posted_at
            $threads->where('threads.posted_at', '<>', '')->orderByDesc('threads.posted_at');
        }
        //关注
        if ($attention == 1 && !empty($this->user)) {
            $threads->leftJoin('user_follow', 'user_follow.to_user_id', '=', 'threads.user_id')
                ->where('user_follow.from_user_id', $this->user->id)
                ->where('is_anonymous', 0);
        }
        !empty($categoryids) && $threads->whereIn('category_id', $categoryids);
        !empty($types) && $threads->whereIn('type', $types);
        $threads = $this->pagination($currentPage, $perPage, $threads);
        return $threads;
    }

    private function getCache($cache, $key)
    {
        $data = $cache->get(CacheKey::LIST_V2_THREADS);
        if ($data) {
            $data = unserialize($data);
            if (isset($data[$key])) {
                return $data[$key];
            }
        }
        return false;
    }

    private function putCache($cache, $key, $threads)
    {
        $data = $cache->get(CacheKey::LIST_V2_THREADS);
        if ($data) {
            $data = unserialize($data);
        } else {
            $data = [];
        }
        $data[$key] = $threads;
        $cache->put(CacheKey::LIST_V2_THREADS, serialize($data), 30 * 60);
    }
}
