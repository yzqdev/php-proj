<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Entities\Activity;
use App\Exceptions\ResourceNotFoundException;
use App\Helpers\Config;
use App\Helpers\Html;
use App\Helpers\JsonResponse;
use App\Helpers\MyLogger;
use App\Helpers\RequestBody;
use App\Middleware\ApiAuthenticate;
use App\Resources\ActivityResource;
use Doctrine\ORM\EntityManager;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ============================================================
 * 活动 API 控制器 — Slim 4 + Doctrine ORM
 * ============================================================
 *
 * 接口清单（RESTful）：
 *   GET    /api/v1/activities           列表（分页，upcoming=true 只返回未来）
 *   GET    /api/v1/activities/{id}      详情
 *   POST   /api/v1/activities           新建（需鉴权）
 *   PUT    /api/v1/activities/{id}      更新（需鉴权）
 *   DELETE /api/v1/activities/{id}      删除（需鉴权）
 */
class ActivityController
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    /**
     * GET /api/v1/activities
     *
     * @summary 活动列表
     * @description 支持分页，upcoming=true 只返回即将开始的活动
     * @tag Activities
     * @parameter page integer 页码
     * @parameter pageSize integer 每页条数
     * @parameter upcoming string 传 true 时只返回未来的活动
     * @response 200 ActivityPage 活动分页列表
     */
    #[OA\Get(
        path: '/activities',
        description: '支持分页，upcoming=true 只返回即将开始的活动',
        summary: '活动列表',
        tags: ['Activities'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: '页码',
                in: 'query',
                schema: new OA\Schema(type: 'integer', default: 1),
            ),
            new OA\Parameter(
                name: 'pageSize',
                description: '每页条数',
                in: 'query',
                schema: new OA\Schema(type: 'integer', default: 8, maximum: 100),
            ),
            new OA\Parameter(
                name: 'upcoming',
                description: '传 true 时只返回未来的活动',
                in: 'query',
                schema: new OA\Schema(type: 'boolean'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '活动分页列表',
//                content: new OA\JsonContent(
//                    ref: '#/components/schemas/ActivityPage',
//                ),
            ),
        ],
    )]
    public function index(ServerRequestInterface $psr7): ResponseInterface
    {
        // Slim 4 用 getQueryParams() 返回整个数组（不像 Slim 3 的 getQueryParam()）
        $params   = $psr7->getQueryParams();
        $page     = max(1, (int)($params['page'] ?? 1));
        $pageSize = min(100, max(1, (int)($params['pageSize'] ?? Config::get('paging.per_page', 8))));
        $upcoming = ($params['upcoming'] ?? '') === 'true';

        $repo  = $this->em->getRepository(Activity::class);
        $list  = $repo->paginate($page, $pageSize, $upcoming);
        $total = $repo->countFiltered($upcoming);

        $pageData = [
            'list'      => ActivityResource::collection($list),
            'total'     => $total,
            'page'      => $page,
            'pageSize'  => $pageSize,
            'totalPage' => $pageSize > 0 ? (int)ceil($total / $pageSize) : 0,
        ];

        return JsonResponse::ok($pageData);
    }

    /**
     * GET /api/v1/activities/{id}
     *
     * @summary 活动详情
     * @description 获取指定 ID 的活动详情
     * @tag Activities
     * @parameter id integer 活动 ID
     * @response 200 Activity 活动详情
     * @response 404 活动不存在
     */
    #[OA\Get(
        path: '/activities/{id}',
        description: '获取指定 ID 的活动详情',
        summary: '活动详情',
        tags: ['Activities'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: '活动 ID',
                schema: new OA\Schema(type: 'integer', minimum: 1),
                example: 1,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '活动详情',
//                content: new OA\JsonContent(
//                    ref: '#/components/schemas/Activity',
//                ),
            ),
            new OA\Response(
                response: 404,
                description: '活动不存在',
//                content: new OA\JsonContent(
//                    ref: '#/components/schemas/ErrorResponse',
//                ),
            ),
        ],
    )]
    public function show(ServerRequestInterface $psr7): ResponseInterface
    {
        $id = (int)$psr7->getAttribute('id', 0);
        $activity = $this->em->find(Activity::class, $id);

        if ($activity === null) {
            throw new ResourceNotFoundException('Activity', $id);
        }

        return JsonResponse::ok(ActivityResource::make($activity));
    }

    /**
     * POST /api/v1/activities（需鉴权）
     *
     * @summary 新建活动
     * @description 需要 Bearer Token
     * @tag Activities
     * @security
     * @requestBody ActivityCreateRequest
     * @response 201 Activity 创建成功
     * @response 401 未登录
     * @response 422 参数校验失败
     */
    #[OA\Post(
        path: '/activities',
        description: '需要 Bearer Token',
        summary: '新建活动',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
//            content: new OA\JsonContent(
//                ref: '#/components/schemas/ActivityCreateRequest',
//            ),
        ),
        tags: ['Activities'],
        responses: [
            new OA\Response(
                response: 201,
                description: '创建成功',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Activity',
                ),
            ),
            new OA\Response(
                response: 401,
                description: '未登录',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
            new OA\Response(
                response: 422,
                description: '参数校验失败',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
        ],
    )]
    public function store(ServerRequestInterface $psr7): ResponseInterface
    {
        $body   = RequestBody::json($psr7);
        $errors = self::validate($body);

        if ($errors !== []) {
            return JsonResponse::validation($errors);
        }

        $activity = new Activity();
        $activity->setTitle(trim($body['title']))
                 ->setLocation(trim($body['location'] ?? ''))
                 ->setStartTime(self::parseDateTime($body['start_time']))
                 ->setEndTime(($body['end_time'] ?? '') !== '' ? self::parseDateTime($body['end_time']) : null)
                 ->setDescription($body['description'] ?? null);

        $this->em->persist($activity);
        $this->em->flush();

        MyLogger::info('活动创建成功', [
            'activityId' => $activity->getId(),
            'title'      => $activity->getTitle(),
            'userId'     => (int)(ApiAuthenticate::currentUser()['userId'] ?? 0),
        ]);

        return JsonResponse::ok(ActivityResource::make($activity), '创建成功', 201);
    }

    /**
     * PUT /api/v1/activities/{id}（需鉴权）
     *
     * @summary 更新活动
     * @description PATCH 语义：只更新提交了字段
     * @tag Activities
     * @security
     * @parameter id integer 活动 ID
     * @requestBody ActivityUpdateRequest
     * @response 200 Activity 更新成功
     * @response 401 未登录
     * @response 404 活动不存在
     * @response 422 参数校验失败
     */
    #[OA\Put(
        path: '/activities/{id}',
        description: 'PATCH 语义：只更新提交了字段',
        summary: '更新活动',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
//            content: new OA\JsonContent(
//                ref: '#/components/schemas/ActivityUpdateRequest',
//            ),
        ),
        tags: ['Activities'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: '活动 ID',
                schema: new OA\Schema(type: 'integer', minimum: 1),
                example: 1,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '更新成功',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Activity',
                ),
            ),
            new OA\Response(
                response: 401,
                description: '未登录',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
            new OA\Response(
                response: 404,
                description: '活动不存在',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
            new OA\Response(
                response: 422,
                description: '参数校验失败',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
        ],
    )]
    public function update(ServerRequestInterface $psr7): ResponseInterface
    {
        $id       = (int)$psr7->getAttribute('id', 0);
        $activity = $this->em->find(Activity::class, $id);
        if ($activity === null) {
            throw new ResourceNotFoundException('Activity', $id);
        }

        $body = RequestBody::json($psr7);

        $touched = false;
        if (isset($body['title']))       { $activity->setTitle(trim($body['title']));       $touched = true; }
        if (isset($body['location']))    { $activity->setLocation(trim($body['location']));  $touched = true; }
        if (isset($body['start_time']))  { $activity->setStartTime(self::parseDateTime($body['start_time'])); $touched = true; }
        if (isset($body['end_time']))    {
            $activity->setEndTime($body['end_time'] !== '' ? self::parseDateTime($body['end_time']) : null);
            $touched = true;
        }
        if (isset($body['description'])) { $activity->setDescription($body['description']); $touched = true; }

        if (!$touched) {
            return JsonResponse::validation(['_' => ['未提供任何字段']]);
        }

        $this->em->flush();

        MyLogger::info('活动更新成功', [
            'activityId' => $id,
            'fields'     => array_keys(array_filter($body, static fn($k) => in_array($k, ['title','location','start_time','end_time','description'], true), ARRAY_FILTER_USE_KEY)),
            'userId'     => (int)(ApiAuthenticate::currentUser()['userId'] ?? 0),
        ]);

        return JsonResponse::ok(ActivityResource::make($activity), '更新成功');
    }

    /**
     * DELETE /api/v1/activities/{id}（需鉴权）
     *
     * @summary 删除活动
     * @tag Activities
     * @security
     * @parameter id integer 活动 ID
     * @response 204 删除成功（无 body）
     * @response 401 未登录
     * @response 404 活动不存在
     */
    #[OA\Delete(
        path: '/activities/{id}',
        summary: '删除活动',
        security: [['bearerAuth' => []]],
        tags: ['Activities'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: '活动 ID',
                schema: new OA\Schema(type: 'integer', minimum: 1),
                example: 1,
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: '删除成功（无 body）',
            ),
            new OA\Response(
                response: 401,
                description: '未登录',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
            new OA\Response(
                response: 404,
                description: '活动不存在',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
        ],
    )]
    public function destroy(ServerRequestInterface $psr7): ResponseInterface
    {
        $id       = (int)$psr7->getAttribute('id', 0);
        $activity = $this->em->find(Activity::class, $id);

        if ($activity === null) {
            throw new ResourceNotFoundException('Activity', $id);
        }

        $this->em->remove($activity);
        $this->em->flush();

        MyLogger::info('活动 deletion 成功', [
            'activityId' => $id,
            'userId'     => (int)(ApiAuthenticate::currentUser()['userId'] ?? 0),
        ]);

        return JsonResponse::noContent();
    }

    // =====================================================================
    // 校验 & 工具
    // =====================================================================

    /**
     * 校验活动数据
     *
     * @return array 字段级错误；空数组表示通过
     */
    private static function validate(array $body): array
    {
        $errors = [];

        $title = (string)($body['title'] ?? '');
        if ($title === '') {
            $errors['title'][] = '标题不能为空';
        } elseif (Html::charLen($title) > 100) {
            $errors['title'][] = '标题最多 100 个字符';
        }

        $startTime = (string)($body['start_time'] ?? '');
        if ($startTime === '') {
            $errors['start_time'][] = '开始时间不能为空';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $startTime)) {
            $errors['start_time'][] = '开始时间格式必须为 YYYY-MM-DD HH:MM:SS';
        }

        $endTime = (string)($body['end_time'] ?? '');
        if ($endTime !== '') {
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $endTime)) {
                $errors['end_time'][] = '结束时间格式必须为 YYYY-MM-DD HH:MM:SS';
            } elseif ($startTime !== '' && $endTime <= $startTime) {
                $errors['end_time'][] = '结束时间必须晚于开始时间';
            }
        }

        return $errors;
    }

    /** 把 'Y-m-d H:i:s' 字符串解析为 DateTimeImmutable */
    private static function parseDateTime(string $value): \DateTimeImmutable
    {
        return new \DateTimeImmutable($value);
    }
}
