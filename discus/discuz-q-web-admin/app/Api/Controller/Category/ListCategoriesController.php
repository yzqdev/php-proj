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

namespace App\Api\Controller\Category;

use App\Api\Serializer\CategorySerializer;
use App\Models\Category;
use App\Models\User;
use Discuz\Api\Controller\AbstractListController;
use Illuminate\Support\Collection;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Exception\InvalidParameterException;
use Illuminate\Support\Arr;
class ListCategoriesController extends AbstractListController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = CategorySerializer::class;

    /**
     * {@inheritdoc}
     */
    public $optionalInclude = ['moderators'];

    /**
     * @param ServerRequestInterface $request
     * @param Document $document
     * @return Collection
     * @throws InvalidParameterException
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        /** @var User $actor */
        $actor = $request->getAttribute('actor');
        $params = $request->getQueryParams();
        $filter = $this->extractFilter($request);
        $include = $this->extractInclude($request);

        $type = Arr::get($params, 'type', '');

        
        $query = Category::query();
        //分类属性 2022-10-13 张安冠
        if($type==2) {
            $query == $query->where('type', 2); // 顶部分类
        } elseif ($type==3) {
            $query == $query->where('type', 3); // 尾部分类
        } elseif ($type==4) {
            $query == $query->where('type', 4);  // 标签分类
        } elseif ($type==5) {
            $query == $query->where('type', 5); // 圈子分类
        } elseif ($type==6) {
            $query == $query->where('type', 6); // 课程分类
        } elseif ($type==7) {
            $query == $query->where('type', 7); // 提问分类
        } elseif ($type==8) {
            $query == $query->where('type', 8); // 商城分类
        } elseif ($type==9) {
            $query == $query->where('type', 9); // 商品分类
        } else {
            $query == $query->where('type', 1); // 社区分类
        }
        //$query == $query->where('type', 3);

        // 根据传参返回可发布内容的分类，否则返回可查看内容的分类
        if($type==7) {
            if ($actor->exists && isset($filter['createaskThread']) && $filter['createaskThread']) {
            $query->whereNotIn('id', Category::getIdsWhereCannot($actor, 'createaskThread'));
            } else {
                $query->whereNotIn('id', Category::getIdsWhereCannot($actor, 'viewaskThreads'));
            }
        } else {
            if ($actor->exists && isset($filter['createThread']) && $filter['createThread']) {
            $query->whereNotIn('id', Category::getIdsWhereCannot($actor, 'createThread'));
            } else {
                $query->whereNotIn('id', Category::getIdsWhereCannot($actor, 'viewThreads'));
            }
        }

        

        $categories = $query->where('parentid',0)->orderBy('sort')->get();

        // 分类版主
        if (in_array('moderators', $include)) {
            $users = User::query()->findMany(
                $categories->pluck('moderators')->flatten()->unique()
            );

            $categories->map(function (Category $category) use ($users) {
                $category->setRelation('moderatorUsers', $users->whereIn('id', $category->moderators));
            });

            // 因关系与字段重名，序列化时使用 moderatorUsers，为避免 loadMissing 异常，移除 moderators
            $include = array_diff($include, ['moderators']);
        }

        return $categories->loadMissing($include);
    }
}
