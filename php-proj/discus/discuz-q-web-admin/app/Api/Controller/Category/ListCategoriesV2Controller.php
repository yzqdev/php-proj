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
 */

namespace App\Api\Controller\Category;

use App\Common\ResponseCode;
use App\Models\Category;
use App\Models\Permission;
use Discuz\Base\DzqController;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Arr;

class ListCategoriesV2Controller extends DzqController
{
    public function main()
    {
        $groups = $this->user->groups->toArray();
        $groupIds = array_column($groups, 'id');
        $permissions = Permission::query()->whereIn('group_id', $groupIds)->get()->toArray();
        $permissions = array_column($permissions, 'permission');
        $type = $this->inPut('type');
        //$type =
        
        $categorytype = Category::query();
        //分类属性 2022-10-13 张安冠
        if($type==2) {
            $categorytype == $categorytype->where('type', 2)->where('show', '<>', 2);
        } elseif ($type==3) {
            $categorytype == $categorytype->where('type', 3)->where('show', '<>', 2);
        } elseif ($type==4) {
            $categorytype == $categorytype->where('type', 4)->where('show', '<>', 2);
        } elseif ($type==5) {
            $categorytype == $categorytype->where('type', 5)->where('show', '<>', 2);
        } elseif ($type==6) {
            $categorytype == $categorytype->where('type', 6)->where('show', '<>', 2);
        } elseif ($type==7) {
            $categorytype == $categorytype->where('type', 7)->where('show', '<>', 2);
        } elseif ($type==8) {
            $categorytype == $categorytype->where('type', 8)->where('show', '<>', 2);
        } elseif ($type==9) {
            $categorytype == $categorytype->where('type', 9)->where('show', '<>', 2);
        } else {
            $categorytype == $categorytype->where('type', 1)->where('show', '<>', 2);
        }
        

        $categories = $categorytype
            ->select([
                'id as pid', 'name', 'description', 'show', 'url', 'icon', 'sort', 'property', 'type', 'thread_count as threadCount', 'parentid'
            ])
            ->orderBy('parentid', 'asc')
            ->orderBy('sort')
            ->get()->toArray();

        $categoriesFather = [];
        $categoriesChild = [];

        foreach ($categories as $category) {
            
            // 全局或单个分类创建权限
            if($type==2) {
                $createThreadPermission = 'category' . $category['pid'] . '.createThread';
            } else {
                $createThreadPermission = 'category' . $category['pid'] . '.createThread';
            }
            if (in_array('createThread', $permissions) || in_array($createThreadPermission, $permissions) || $this->user->isAdmin()) {
                $category['canCreateThread'] = true;
            } else {
                $category['canCreateThread'] = false;
            }

            $category['searchIds'] = (int)$category['pid'];

            // 二级子类集合
            if ($category['parentid'] !== 0) {
                $categoriesChild[$category['parentid']][] = $category;
            }

            // 一级分类 --- 全局或单个分类查看权限
            if($type==2) {
                $categoriesFather[] = $category;
            } elseif ($type==3) {
                $categoriesFather[] = $category;
            } elseif ($type==4) {
                $categoriesFather[] = $category;
            } elseif ($type==7) {
                $categoriesFather[] = $category;
            } else {
                $viewPermission = 'category' . $category['pid'] . '.viewThreads';
                if ($category['parentid'] == 0 && (in_array('viewThreads', $permissions) || in_array($viewPermission, $permissions) || $this->user->isAdmin())) {
                    $categoriesFather[] = $category;
                }
            }
        }
        // 获取一级分类的二级子类
        foreach ($categoriesFather as $key => $value) {

            if (isset($categoriesChild[$value['pid']])) {
                $categoriesFather[$key]['searchIds'] = array_merge([$value['searchIds']], array_column($categoriesChild[$value['pid']], 'pid'));
                $categoriesFather[$key]['children'] = $categoriesChild[$value['pid']];
            } else {
                $categoriesFather[$key]['children'] = [];
            }
        }
        $this->outPut(ResponseCode::SUCCESS, '', $categoriesFather);
    }
}
