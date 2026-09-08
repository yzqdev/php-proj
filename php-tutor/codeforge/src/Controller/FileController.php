<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\File;
use App\Exception\FileUploadException;
use App\Response\BaseResponse;
use App\Service\FileService;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;

/**
 * 文件管理控制器（Slim PSR-7 风格）
 *
 * 纯 RESTful API 控制器，只返回 JSON。
 * 不再负责服务端页面渲染，前端由 Vue 3 独立处理。
 *
 * 依赖注入：FileService、Logger 由 PHP-DI autowiring 自动解析。
 */
final class FileController
{
    /** 列表条数的上下界（防止大请求拖垮数据库） */
    private const MIN_LIMIT = 1;
    private const MAX_LIMIT = 500;

    /** 下载流式的分块大小（字节） */
    private const READ_CHUNK = 8192;

    public function __construct(
        private FileService $service,
        private readonly Logger $logger,
    ) {
    }

    /**
     * GET /api/files — 文件列表
     *
     * 查询参数：limit（可选，1..500，默认 100）
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     *
     * @return Response JSON 响应，格式：{ code: 0, message: "获取成功", data: [...] }
     */
    public function index(Request $request, Response $response): Response
    {
        $this->logger->info('获取文件列表');

        $files = $this->service->getAllFiles($this->limitFromQuery($request));
        $data = array_map(fn(File $f) => $f->toArray(), $files);

        return BaseResponse::success($response, $data, '获取成功');
    }

    /**
     * GET /api/files/{id} — 文件详情
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     * @param array    $args     路由参数，包含 {id}
     *
     * @return Response JSON 响应，文件不存在返回 404
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $this->logger->info('获取单个文件', ['id' => $args['id']]);

        $file = $this->service->getFile((int) $args['id']);
        if ($file === null) {
            return BaseResponse::notFound($response, '文件不存在');
        }

        return BaseResponse::success($response, $file->toArray(), '获取成功');
    }

    /**
     * POST /api/files — 上传文件
     *
     * 请求体：multipart/form-data，字段名为 file。
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     *
     * @return Response 成功返回 201 + 文件记录；参数或校验失败返回 422
     */
    public function upload(Request $request, Response $response): Response
    {
        $uploads = $request->getUploadedFiles();
        $uploaded = $uploads['file'] ?? null;

        if ($uploaded === null) {
            $this->logger->warning('上传失败：缺少 file 字段');
            return BaseResponse::validationError($response, '请通过 multipart/form-data 的 file 字段提交文件');
        }

        if (!$uploaded instanceof UploadedFileInterface) {
            return BaseResponse::validationError($response, 'file 字段必须是文件');
        }

        try {
            $file = $this->service->upload($uploaded);
        } catch (FileUploadException $e) {
            $this->logger->warning('文件上传被拒绝', ['error' => $e->getMessage()]);
            return BaseResponse::validationError($response, $e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error('文件上传异常', ['error' => $e->getMessage()]);
            return BaseResponse::error($response, '上传失败，请稍后重试', 500, null, 500);
        }

        $this->logger->info('文件上传成功', [
            'id'   => $file->getId(),
            'name' => $file->getOriginalName(),
            'size' => $file->getSize(),
        ]);

        return BaseResponse::success($response, $file->toArray(), '上传成功', 201);
    }

    /**
     * DELETE /api/files/{id} — 删除文件
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     * @param array    $args     路由参数，包含 {id}
     *
     * @return Response 成功返回 200，文件不存在返回 404
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $this->logger->info('删除文件请求', ['id' => $id]);

        if (!$this->service->deleteFile($id)) {
            return BaseResponse::notFound($response, '文件不存在');
        }

        return BaseResponse::success($response, null, ' ');
    }

    /**
     * GET /api/files/{id}/download — 下载文件（流式输出）
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     * @param array    $args     路由参数，包含 {id}
     *
     * @return Response 二进制响应，带 Content-Disposition attachment 头
     */
    public function download(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $file = $this->service->getFile($id);
        if ($file === null) {
            return BaseResponse::notFound($response, '文件不存在');
        }

        $absolute = $this->service->storagePath($file);
        if ($absolute === null) {
            $this->logger->warning('文件记录存在但磁盘文件缺失', ['id' => $id, 'path' => $file->getPath()]);
            return BaseResponse::notFound($response, '文件已丢失');
        }

        $size = (int) filesize($absolute);

        $this->logger->info('下载文件', ['id' => $id, 'name' => $file->getOriginalName(), 'size' => $size]);

        return $this->sendFile($response, $file, $absolute, $size);
    }

    /**
     * 从查询参数读取列表条数，收敛到 MIN_LIMIT..MAX_LIMIT 之间
     *
     * @param Request $request PSR-7 HTTP 请求对象
     *
     * @return int 收敛后的条数
     */
    private function limitFromQuery(Request $request): int
    {
        $params = $request->getQueryParams();
        $limit = (int) ($params['limit'] ?? FileService::DEFAULT_LIMIT);

        return max(self::MIN_LIMIT, min($limit, self::MAX_LIMIT));
    }

    /**
     * 分块读取磁盘文件写入响应体，并附加下载所需的响应头
     *
     * @param Response $response     PSR-7 响应对象
     * @param File     $file         文件记录（提供原始文件名与 MIME）
     * @param string   $absolutePath 文件绝对路径
     * @param int      $size         文件字节数
     *
     * @return Response 已完成写入的响应
     */
    private function sendFile(Response $response, File $file, string $absolutePath, int $size): Response
    {
        $body = $response->getBody();
        $body->rewind();

        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            $this->logger->error('下载文件打开失败', ['path' => $absolutePath]);
            return BaseResponse::error($response, '文件读取失败', 500, null, 500);
        }

        try {
            while (!feof($handle)) {
                $chunk = fread($handle, self::READ_CHUNK);
                if ($chunk === false) {
                    break;
                }
                $body->write($chunk);
            }
        } finally {
            fclose($handle);
        }

        $mime = $file->getMime() !== '' ? $file->getMime() : 'application/octet-stream';
        $name = $file->getOriginalName();

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', $mime)
            ->withHeader('Content-Length', (string) $size)
            ->withHeader('Cache-Control', 'public, max-age=86400')
            ->withHeader('Content-Disposition', sprintf(
                'attachment; filename="%s"; filename*=UTF-8\'\'%s',
                $this->asciiFilename($name),
                rawurlencode($name),
            ));
    }

    /**
     * 生成 Content-Disposition 的 ASCII 兜底文件名
     *
     * 现代浏览器优先使用 filename* 的 UTF-8 值，
     * filename 只作为旧客户端的降级，非 ASCII 字符替换为下划线。
     *
     * @param string $name 原始文件名
     *
     * @return string 仅含可打印 ASCII 的文件名
     */
    private function asciiFilename(string $name): string
    {
        $ascii = preg_replace('/[^\x20-\x7E]/', '_', $name);
        $ascii = is_string($ascii) ? $ascii : $name;
        $ascii = trim(str_replace('"', '', $ascii));

        return $ascii === '' ? 'download' : mb_substr($ascii, 0, 100, 'UTF-8');
    }
}