<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\BaseResponse;
use App\Service\UserService;
use Monolog\Logger;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 用户控制器（Slim PSR-7 风格）
 *
 * 纯 RESTful API 控制器，只返回 JSON。
 * 不再负责服务端页面渲染，前端由 Vue 3 独立处理。
 *
 * 依赖注入：UserService、Logger 由 PHP-DI autowiring 自动解析。
 */
final class UserController
{
    public function __construct(
        private UserService $service,
        private readonly Logger $logger,
    ) {
    }

    /**
     * GET /api/users — 获取用户列表
     *
     * 查询参数：
     *   page    页码（默认 1）
     *   pageSize 每页条数（默认 20，最大 100）
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     *
     * @return Response JSON 响应，格式：{ code: 0, message: "获取成功", data: {...} }
     */
    public function index(Request $request, Response $response): Response
    {
        $this->logger->info('获取用户列表');

        $params = $request->getQueryParams();
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['pageSize'] ?? 20)));

        $result = $this->service->paginateUsers($page, $pageSize);

        return BaseResponse::success($response, $result, '获取成功');
    }

    /**
     * GET /api/users/{id} — 获取单个用户
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     * @param array    $args     路由参数，包含 {id}
     *
     * @return Response JSON 响应，用户不存在时返回 404
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $this->logger->info('获取单个用户', ['id' => $args['id']]);

        $user = $this->service->getUser((int) $args['id']);
        if ($user === null) {
            return BaseResponse::notFound($response, '用户不存在');
        }

        return BaseResponse::success($response, $user->toArray(), '获取成功');
    }

    /**
     * POST /api/users — 创建新用户
     *
     * 请求体：{ "name": "姓名", "email": "邮箱" }
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     *
     * @return Response 成功返回 201 + 新用户数据，参数缺失返回 422，邮箱重复返回 422
     */
    public function create(Request $request, Response $response): Response
    {
        $data = $this->getJsonBody($request);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');

        if ($name === '' || $email === '') {
            $this->logger->warning('创建用户失败：参数缺失', ['name' => $name, 'email' => $email]);
            return BaseResponse::validationError($response, 'name 和 email 不能为空');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return BaseResponse::validationError($response, '邮箱格式不正确');
        }

        $this->logger->info('创建用户', ['name' => $name, 'email' => $email]);

        try {
            $user = $this->service->createUser($name, $email);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->logger->warning('创建用户失败：邮箱已存在', ['email' => $email]);
            return BaseResponse::validationError($response, '该邮箱已被注册');
        } catch (\Throwable $e) {
            $this->logger->error('创建用户异常', ['error' => $e->getMessage()]);
            return BaseResponse::error($response, '创建失败，请稍后重试', 500, null, 500);
        }

        return BaseResponse::success($response, $user->toArray(), '创建成功', 201);
    }

    /**
     * PUT /api/users/{id} — 更新用户
     *
     * 请求体：{ "name": "新姓名", "email": "新邮箱" }（字段可选，只传需要更新的）
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     * @param array    $args     路由参数，包含 {id}
     *
     * @return Response 成功返回更新后数据，参数缺失返回 422，用户不存在返回 404
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->getJsonBody($request);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');

        if ($name === '' && $email === '') {
            return BaseResponse::validationError($response, '至少需要更新一个字段');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return BaseResponse::validationError($response, '邮箱格式不正确');
        }

        $this->logger->info('更新用户', ['id' => $args['id']]);

        try {
            $user = $this->service->updateUser((int) $args['id'], $name, $email);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->logger->warning('更新用户失败：邮箱已被他人使用', ['id' => $args['id'], 'email' => $email]);
            return BaseResponse::validationError($response, '该邮箱已被他人使用');
        } catch (\Throwable $e) {
            $this->logger->error('更新用户异常', ['error' => $e->getMessage()]);
            return BaseResponse::error($response, '更新失败，请稍后重试', 500, null, 500);
        }

        if ($user === null) {
            return BaseResponse::notFound($response, '用户不存在');
        }

        return BaseResponse::success($response, $user->toArray(), '更新成功');
    }

    /**
     * DELETE /api/users/{id} — 删除用户
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     * @param array    $args     路由参数，包含 {id}
     *
     * @return Response 成功返回 200，用户不存在返回 404
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->logger->info('删除用户请求', ['id' => $args['id']]);

        if (!$this->service->deleteUser((int) $args['id'])) {
            return BaseResponse::notFound($response, '用户不存在');
        }

        return BaseResponse::success($response, null, ' ');
    }

    /**
     * GET /pelican — 鹈鹕骑单车动态 SVG
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     *
     * @return Response SVG 图片响应
     */
    public function pelican(Request $request, Response $response): Response
    {
        $svg = <<<'SVG'
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 220" width="500" height="220">
  <defs>
    <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#87CEEB"/>
      <stop offset="100%" stop-color="#E0F7FF"/>
    </linearGradient>
    <linearGradient id="body" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#FFFFFF"/>
      <stop offset="100%" stop-color="#F0F0F0"/>
    </linearGradient>
  </defs>

  <!-- 背景 -->
  <rect width="500" height="220" fill="url(#sky)"/>
  <!-- 云朵 -->
  <g fill="#FFFFFF" opacity="0.8">
    <ellipse cx="80" cy="50" rx="25" ry="15"/>
    <ellipse cx="100" cy="48" rx="20" ry="12"/>
    <ellipse cx="380" cy="60" rx="25" ry="15"/>
    <ellipse cx="400" cy="58" rx="20" ry="12"/>
  </g>
  <!-- 地面 -->
  <rect x="0" y="170" width="500" height="50" fill="#7BC86A"/>

  <!-- 自行车后轮 -->
  <g>
    <circle cx="165" cy="155" r="30" fill="none" stroke="#333" stroke-width="4"/>
    <circle cx="165" cy="155" r="4" fill="#333"/>
    <g>
      <animateTransform attributeName="transform" type="rotate"
        from="0 165 155" to="360 165 155" dur="1.5s" repeatCount="indefinite"/>
      <g>
        <line x1="165" y1="155" x2="180" y2="140" stroke="#555" stroke-width="3"/>
        <line x1="165" y1="155" x2="150" y2="140" stroke="#555" stroke-width="3"/>
        <line x1="165" y1="155" x2="165" y2="125" stroke="#555" stroke-width="3"/>
        <line x1="165" y1="155" x2="165" y2="185" stroke="#555" stroke-width="3"/>
        <line x1="165" y1="155" x2="195" y2="155" stroke="#555" stroke-width="3"/>
        <line x1="165" y1="155" x2="135" y2="155" stroke="#555" stroke-width="3"/>
      </g>
    </g>
  </g>

  <!-- 自行车前轮 -->
  <g>
    <circle cx="335" cy="155" r="30" fill="none" stroke="#333" stroke-width="4"/>
    <circle cx="335" cy="155" r="4" fill="#333"/>
    <g>
      <animateTransform attributeName="transform" type="rotate"
        from="0 335 155" to="360 335 155" dur="1.2s" repeatCount="indefinite"/>
      <g>
        <line x1="335" y1="155" x2="350" y2="140" stroke="#555" stroke-width="3"/>
        <line x1="335" y1="155" x2="320" y2="140" stroke="#555" stroke-width="3"/>
        <line x1="335" y1="155" x2="335" y2="125" stroke="#555" stroke-width="3"/>
        <line x1="335" y1="155" x2="335" y2="185" stroke="#555" stroke-width="3"/>
        <line x1="335" y1="155" x2="365" y2="155" stroke="#555" stroke-width="3"/>
        <line x1="335" y1="155" x2="305" y2="155" stroke="#555" stroke-width="3"/>
      </g>
    </g>
  </g>

  <!-- 车架 -->
  <g stroke="#5A4" stroke-width="5" fill="none">
    <line x1="165" y1="155" x2="335" y2="155"/>
    <line x1="165" y1="155" x2="250" y2="110"/>
    <line x1="335" y1="155" x2="250" y2="110"/>
    <line x1="250" y1="110" x2="250" y2="155"/>
  </g>

  <!-- 车把 -->
  <g stroke="#333" stroke-width="4" fill="none">
    <line x1="335" y1="155" x2="335" y2="120"/>
    <line x1="335" y1="120" x2="315" y2="105"/>
    <line x1="335" y1="120" x2="355" y2="105"/>
  </g>

  <!-- 踏板 -->
  <g>
    <line x1="250" y1="155" x2="250" y2="185" stroke="#333" stroke-width="4"/>
    <line x1="240" y1="185" x2="260" y2="185" stroke="#333" stroke-width="4"/>
    <animateTransform attributeName="transform" type="rotate"
      from="0 250 155" to="360 250 155" dur="1.5s" repeatCount="indefinite"/>
  </g>

  <!-- 鹈鹕身体 -->
  <g>
    <!-- 身体 -->
    <ellipse cx="250" cy="120" rx="45" ry="35" fill="url(#body)" stroke="#CCC" stroke-width="2"/>
    <!-- 翅膀 -->
    <ellipse cx="215" cy="115" rx="22" ry="28" fill="#F5F5F5" stroke="#DDD" stroke-width="1">
      <animateTransform attributeName="transform" type="rotate"
        from="-15 215 115" to="15 215 115" dur="0.8s" repeatCount="indefinite" additive="sum"/>
    </ellipse>
    <!-- 尾羽 -->
    <path d="M295,125 Q310,115 305,135 Q298,140 290,130" fill="#F0F0F0" stroke="#DDD" stroke-width="1"/>
    <!-- 腿 -->
    <g stroke="#E6A23C" stroke-width="4" fill="none">
      <line x1="240" y1="140" x2="235" y2="170">
        <animate attributeName="y2" values="170;165;170" dur="0.8s" repeatCount="indefinite"/>
      </line>
      <line x1="260" y1="140" x2="265" y2="170">
        <animate attributeName="y2" values="165;170;165" dur="0.8s" repeatCount="indefinite"/>
      </line>
    </g>
    <!-- 脚蹼 -->
    <g fill="#E6A23C">
      <ellipse cx="233" cy="170" rx="8" ry="4"/>
      <ellipse cx="267" cy="170" rx="8" ry="4"/>
    </g>
  </g>

  <!-- 鹈鹕头部 -->
  <g>
    <!-- 头部 -->
    <circle cx="285" cy="100" r="22" fill="url(#body)" stroke="#CCC" stroke-width="2"/>
    <!-- 眼睛 -->
    <circle cx="292" cy="95" r="4" fill="#333"/>
    <circle cx="293" cy="94" r="1.5" fill="#FFF"/>
    <!-- 嘴（喙） -->
    <path d="M305,100 L355,92 L350,100 L305,108 Z" fill="#F5A623" stroke="#D4922A" stroke-width="1.5"/>
    <!-- 喉囊 -->
    <path d="M305,100 Q330,118 350,100 Q330,112 305,100 Z" fill="#FFE0B0" stroke="#E6A23C" stroke-width="1"/>
    <!-- 喉囊动画 -->
    <path d="M305,100 Q330,118 350,100 Q330,112 305,100 Z" fill="#FFE0B0" opacity="0.6">
      <animate attributeName="opacity" values="0.3;0.7;0.3" dur="1.5s" repeatCount="indefinite"/>
    </path>
  </g>

  <!-- 鸭舌帽 -->
  <path d="M272,82 Q285,72 300,82 L300,90 L272,90 Z" fill="#E74C3C" stroke="#C0392B" stroke-width="1"/>
  <animateTransform attributeName="transform" type="rotate"
    from="-3 285 100" to="3 285 100" dur="2s" repeatCount="indefinite" additive="sum"/>
</svg>
SVG;
        $response->getBody()->write($svg);
        return $response->withHeader('Content-Type', 'image/svg+xml; charset=utf-8');
    }

    /**
     * 读取 JSON 请求体并解码为数组
     *
     * @param Request $request PSR-7 HTTP 请求对象
     *
     * @return array 解码后的关联数组，非 JSON 或解析失败时返回空数组
     */
    private function getJsonBody(Request $request): array
    {
        $body = (string) $request->getBody();
        $data = json_decode($body, true);
        return is_array($data) ? $data : [];
    }
}