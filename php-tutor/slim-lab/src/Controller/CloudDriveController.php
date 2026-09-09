<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ApiException;
use App\Service\CloudDriveService;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\Stream;

/**
 * /api/clouddrive 控制器（兼容别名 /api/clouddrive.php）：仅做 action 分发与流式输出。
 */

final class CloudDriveController
{
    /** 无登录要求的 action（与旧代码一致：logout/check 不需要登录；分享访客接口见 AccessRules） */
    public const PUBLIC_ACTIONS = ['login', 'logout', 'check', 'stream', 'share_info', 'share_download', 'share_zip', 'share_stream'];

    public function __construct(
        private readonly CloudDriveService $service,
    ) {
    }
    #[OA\Get(
        path: '/api/clouddrive',
        summary: '网盘操作（读接口与文件流）',
        security: [['SessionCookie' => []]],
        tags: ['CloudDrive'],
        parameters: [
            new OA\Parameter(
                name: 'action',
                in: 'query',
                required: true,
                description: '操作类型',
                schema: new OA\Schema(
                    type: 'string',
                    enum: [
                        'login',
                        'logout',
                        'check',
                        'list_dir',
                        'download',
                        'zip',
                        'stream',
                        'share_info',
                        'upload',
                        'mkdir',
                        'delete',
                        'rename',
                        'move',
                    ],
                    example: 'list_dir'
                )
            ),
            new OA\Parameter(
                name: 'pwd',
                in: 'query',
                description: '密码（login 时）',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'dir',
                in: 'query',
                description: '目录路径（list_dir 时）',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'file',
                in: 'query',
                description: '文件名（download/stream 时）',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '成功',
                content: new OA\JsonContent(ref: '#/components/schemas/CloudDriveGetData')
            ),
            new OA\Response(response: '401', description: '未登录'),
            new OA\Response(response: '405', description: '方法不允许'),
            new OA\Response(response: '500', description: '服务器错误'),
        ]
    )]
    public function handle(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $action = (string) ($params['action'] ?? '');
        $method = $request->getMethod();

        return match ($action) {
            'login' => $this->requirePost($method, $response, fn() => $this->login($request, $response)),
            'logout' => ResponseFactory::ok($response, $this->service->logout(), '已退出登录'),
            'check' => ResponseFactory::ok($response, $this->service->check()),
            // ---------------- 分享访客接口（免登录，凭 token + 提取密码访问） ----------------
            'share_info' => $this->requirePost($method, $response, fn() => $this->shareInfo($request, $response)),
            'share_download' => $this->shareDownload($params, $response),
            'share_zip' => $this->shareZip($params, $response),
            'share_stream' => $this->shareStream($params, $response),
            'global_search' => $this->requirePost($method, $response, fn() => $this->globalSearch($request, $response)),
            'create_share' => $this->requirePost($method, $response, fn() => $this->createShare($request, $response)),
            'get_share_list' => ResponseFactory::ok($response, $this->service->getShareList()),
            'delete_share' => $this->requirePost($method, $response, fn() => $this->deleteShare($request, $response)),
            'mkdir' => $this->requirePost($method, $response, fn() => $this->mkdir($request, $response)),
            'upload' => $this->requirePost($method, $response, fn() => $this->upload($request, $response)),
            'change_pwd' => $this->requirePost($method, $response, fn() => $this->changePwd($request, $response)),
            'list_dir' => ResponseFactory::ok($response, $this->service->listDir(
                (string) ($params['dir'] ?? ''),
                (string) ($params['sortby'] ?? ''),
                (string) ($params['sortorder'] ?? ''),
            )),
            'delete' => $this->requirePost($method, $response, fn() => $this->delete($request, $response)),
            'batch_delete' => $this->requirePost($method, $response, fn() => $this->batchDelete($request, $response)),
            'rename' => $this->requirePost($method, $response, fn() => $this->rename($request, $response)),
            'move' => $this->requirePost($method, $response, fn() => $this->move($request, $response)),
            'download' => $this->download($params, $response),
            'zip' => $this->zip($params, $response),
            'stream' => $this->stream($params, $response),
            default => throw new ApiException('未知操作'),
        };
    }

    private function requirePost(string $method, ResponseInterface $response, callable $next): ResponseInterface
    {
        if ($method !== 'POST') {
            return ResponseFactory::error($response, '方法不允许', 405);
        }

        return $next();
    }

    /**
     * 等价旧代码 trim($_POST[$key] ?? '')。
     */
    private function field(ServerRequestInterface $request, string $key): string
    {
        $body = (array) $request->getParsedBody();

        return trim((string) ($body[$key] ?? ''));
    }

    private function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // 密码错误时 Service 抛 ApiException('密码错误')，与旧 apiError 一致
        $result = $this->service->login($this->field($request, 'pwd'));

        return ResponseFactory::ok($response, $result, '登录成功');
    }

    private function globalSearch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return ResponseFactory::ok($response, $this->service->globalSearch($this->field($request, 'kw')));
    }

    private function createShare(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $result = $this->service->createShare(
            $this->field($request, 'current_folder'),
            $this->field($request, 'share_file'),
            (($body['is_dir_share'] ?? '0') === '1'),
            (int) ($body['share_expire'] ?? 0),
            $this->field($request, 'share_pwd'),
        );

        return ResponseFactory::ok($response, $result);
    }

    private function deleteShare(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->service->deleteShare($this->field($request, 'token'));

        return ResponseFactory::ok($response, null, '删除成功');
    }

    /** 分享信息查询：token + 可选提取密码（未传时返回 need_pwd=true） */
    private function shareInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return ResponseFactory::ok($response, $this->service->shareInfo(
            $this->field($request, 'token'),
            $this->field($request, 'share_pwd'),
        ));
    }

    /** 分享文件下载：复用 download 的响应头逻辑，仅换公开鉴权 */
    private function shareDownload(array $params, ResponseInterface $response): ResponseInterface
    {
        $resolved = $this->service->resolveShare(
            (string) ($params['token'] ?? ''),
            (string) ($params['share_pwd'] ?? ''),
        );
        if ($resolved['is_dir']) {
            throw new ApiException('目录请使用打包下载', 400);
        }
        $file = $this->service->resolveDownload($resolved['dir'], $resolved['name']);

        return $response
            ->withHeader('Content-Type', 'application/octet-stream;charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment;filename=' . rawurlencode($file['name']))
            ->withBody($this->openStream($file['path']));
    }

    /** 分享目录打包下载：复用 zip 逻辑与临时文件清理 */
    private function shareZip(array $params, ResponseInterface $response): ResponseInterface
    {
        $resolved = $this->service->resolveShare(
            (string) ($params['token'] ?? ''),
            (string) ($params['share_pwd'] ?? ''),
        );
        if (!$resolved['is_dir']) {
            throw new ApiException('文件请直接下载', 400);
        }
        $zip = $this->service->createZip($resolved['dir'], $resolved['name']);
        $zipFile = $zip['file'];

        register_shutdown_function(static function () use ($zipFile): void {
            if (is_file($zipFile)) {
                unlink($zipFile);
            }
        });

        return $response
            ->withHeader('Content-Type', 'application/zip;charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment;filename=' . rawurlencode($zip['name']) . '.zip')
            ->withBody($this->openStream($zipFile));
    }

    /** 分享文件在线预览（图片/视频/PDF）：复用 stream 的响应头逻辑 */
    private function shareStream(array $params, ResponseInterface $response): ResponseInterface
    {
        $resolved = $this->service->resolveShare(
            (string) ($params['token'] ?? ''),
            (string) ($params['share_pwd'] ?? ''),
        );
        if ($resolved['is_dir']) {
            throw new ApiException('目录不支持预览', 400);
        }
        $full = $this->service->resolveStream($resolved['dir'] !== '' ? $resolved['dir'] . '/' . $resolved['name'] : $resolved['name']);
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $contentType = match (true) {
            $this->service->isImageExt($ext) => 'image/*',
            $this->service->isVideoExt($ext) => 'video/*',
            $this->service->isPdfExt($ext) => 'application/pdf',
            default => 'application/octet-stream',
        };

        return $response
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET,OPTIONS')
            ->withBody($this->openStream($full));
    }

    private function mkdir(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $message = $this->service->mkdir($this->field($request, 'current_folder'), $this->field($request, 'target_dir'));

        return ResponseFactory::ok($response, null, $message);
    }

    private function upload(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $file = $request->getUploadedFiles()['file'] ?? null;
        if (!$file instanceof UploadedFileInterface) {
            throw new ApiException('未检测到文件');
        }
        // 与旧逻辑一致：上传出错/空文件统一报"文件为空"，未带文件字段报"未检测到文件"
        if ($file->getError() !== UPLOAD_ERR_OK || ($file->getSize() !== null && $file->getSize() <= 0)) {
            throw new ApiException('文件为空');
        }
        $this->service->upload(
            $this->field($request, 'current_folder'),
            [
                'name' => (string) ($file->getClientFilename() ?? ''),
                'tmp_name' => $file->getFilePath(),
                'size' => $file->getSize() ?? 0,
            ],
            $this->field($request, 'relative_path'),
        );

        return ResponseFactory::ok($response, null, '上传成功');
    }

    private function changePwd(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->service->changePwd($this->field($request, 'new_pwd1'), $this->field($request, 'new_pwd2'));

        return ResponseFactory::ok($response, null, '密码修改成功，请重新登录');
    }

    private function delete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->service->delete($this->field($request, 'current_folder'), $this->field($request, 'name'));

        return ResponseFactory::ok($response, null, '删除成功');
    }

    private function batchDelete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $batchList = $body['batch_list'] ?? [];
        if (!is_array($batchList)) {
            $batchList = [$batchList];
        }
        $this->service->batchDelete($this->field($request, 'current_folder'), $batchList);

        return ResponseFactory::ok($response, null, '批量删除成功');
    }

    private function rename(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->service->rename(
            $this->field($request, 'current_folder'),
            $this->field($request, 'old_name'),
            $this->field($request, 'new_name'),
        );

        return ResponseFactory::ok($response, null, '重命名成功');
    }

    private function move(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->service->move(
            $this->field($request, 'current_folder'),
            $this->field($request, 'src_name'),
            $this->field($request, 'dst_dir'),
        );

        return ResponseFactory::ok($response, null, '移动成功');
    }

    /**
     * GET download：流式输出文件。header 与旧代码一致。
     */
    private function download(array $params, ResponseInterface $response): ResponseInterface
    {
        $resolved = $this->service->resolveDownload((string) ($params['dir'] ?? ''), (string) ($params['file'] ?? ''));

        return $response
            ->withHeader('Content-Type', 'application/octet-stream;charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment;filename=' . rawurlencode($resolved['name']))
            ->withBody($this->openStream($resolved['path']));
    }

    /**
     * GET zip：打包目录后流式输出。header 与旧代码一致；临时 zip 在响应发送完成后清理。
     */
    private function zip(array $params, ResponseInterface $response): ResponseInterface
    {
        $resolved = $this->service->createZip((string) ($params['dir'] ?? ''), (string) ($params['folder'] ?? ''));
        $zipFile = $resolved['file'];

        // 旧代码 readfile 后 unlink；这里等价：响应发送结束后删除临时 zip
        register_shutdown_function(static function () use ($zipFile): void {
            if (is_file($zipFile)) {
                unlink($zipFile);
            }
        });

        return $response
            ->withHeader('Content-Type', 'application/zip;charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment;filename=' . rawurlencode($resolved['name']) . '.zip')
            ->withBody($this->openStream($zipFile));
    }

    /**
     * GET stream：按扩展名输出图片/视频/PDF。跨域 header 与旧代码一致（* + GET,OPTIONS）。
     */
    private function stream(array $params, ResponseInterface $response): ResponseInterface
    {
        $full = $this->service->resolveStream((string) ($params['file'] ?? ''));
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        if ($this->service->isImageExt($ext)) {
            $contentType = 'image/*';
        } elseif ($this->service->isVideoExt($ext)) {
            $contentType = 'video/*';
        } elseif ($this->service->isPdfExt($ext)) {
            $contentType = 'application/pdf';
        } else {
            $contentType = 'application/octet-stream';
        }

        return $response
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET,OPTIONS')
            ->withBody($this->openStream($full));
    }

    private function openStream(string $path): Stream
    {
        $resource = fopen($path, 'rb');
        if ($resource === false) {
            throw new ApiException('文件读取失败');
        }

        return new Stream($resource);
    }
}
