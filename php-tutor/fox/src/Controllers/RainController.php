<?php

namespace Yzqde\Fox\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yzqde\Fox\Support\BaseResponse;
use Yzqde\Fox\Util\FileUtil;
use OpenApi\Attributes as OA;
use Yzqde\Fox\Vo\FileVo;
use Yzqde\Fox\Vo\DirectoryItemVo;
use Yzqde\Fox\Vo\FileContentVo;
use Yzqde\Fox\Vo\FileExistsVo;
use Yzqde\Fox\Vo\FileInfoVo;
use Yzqde\Fox\Vo\FileOperationVo;

final class RainController
{
    #[OA\Get(
        path: "/api/rain/getIndex",
        operationId: "getIndex",
        summary: "获取项目根目录",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function getIndex(
        Request $request,
        Response $response
    ): Response {
        $path = dirname(__DIR__, 2);

        return BaseResponse::success(
            $response,
            new FileVo(
                $path,
                "项目根目录"
            )
        );
    }


    #[OA\Get(
        path: "/api/rain/fileExists",
        operationId: "fileExists",
        summary: "判断文件是否存在",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function fileExists(
        Request $request,
        Response $response
    ): Response {
        $path = dirname(__DIR__, 2) . '/composer.json';

        return BaseResponse::success(
            $response,
            new FileExistsVo(
                path: realpath($path),
                exists: file_exists($path),
                isFile: is_file($path),
                isDirectory: is_dir($path),
            )
        );
    }


    #[OA\Get(
        path: "/api/rain/readFile",
        operationId: "readFile",
        summary: "读取文件",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function readFile(
        Request $request,
        Response $response
    ): Response {
        $path = dirname(__DIR__, 2) . '/composer.json';

        if (!is_file($path)) {
            return BaseResponse::error(
                $response,
                "文件不存在"
            );
        }

        return BaseResponse::success(
            $response,
            new FileContentVo(
                path: $path,
                content: file_get_contents($path),
            )
        );
    }


    #[OA\Post(
        path: "/api/rain/writeFile",
        operationId: "writeFile",
        summary: "写入文件",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function writeFile(
        Request $request,
        Response $response
    ): Response {
        $path = dirname(__DIR__, 2) . '/logs/runtime/test.txt';

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $success = file_put_contents(
                $path,
                "Hello PHP\n"
            ) !== false;

        return BaseResponse::success(
            $response,
            new FileOperationVo(
                path: $path,
                success: $success,
            )
        );
    }


    #[OA\Post(
        path: "/api/rain/appendFile",
        operationId: "appendFile",
        summary: "追加文件内容",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function appendFile(
        Request $request,
        Response $response
    ): Response {
        $path = dirname(__DIR__, 2) . '/runtime/test.txt';

        $success = file_put_contents(
                $path,
                "追加内容：" . date('Y-m-d H:i:s') . PHP_EOL,
                FILE_APPEND
            ) !== false;

        return BaseResponse::success(
            $response,
            new FileOperationVo(
                path: $path,
                success: $success,
            )
        );
    }


    #[OA\Get(
        path: "/api/rain/getTempFolder",
        operationId: "getTempFolder",
        summary: "获取文件信息",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function getTempFolder(   Request $request,
                                     Response $response)
    {
         $tmp=sys_get_temp_dir();
         return BaseResponse::success($response, $tmp);
    }
    #[OA\Get(
        path: "/api/rain/fileInfo",
        operationId: "fileInfo",
        summary: "获取文件信息",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function fileInfo(
        Request $request,
        Response $response
    ): Response {
        $path = dirname(__DIR__, 2) . '/composer.json';

        return BaseResponse::success(
            $response,
            new FileInfoVo(
                path: $path,
                exists: file_exists($path),
                size: filesize($path),
                modifiedTime: date('Y-m-d H:i:s', filemtime($path)),
                permissions: substr(
                    sprintf('%o', fileperms($path)),
                    -4
                ),
                type: filetype($path),
            )
        );
    }


    #[OA\Get(
        path: "/api/rain/listDirectory",
        operationId: "listDirectory",
        summary: "遍历目录",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function listDirectory(
        Request $request,
        Response $response
    ): Response {
        $path = dirname(__DIR__, 2) . '/src';

        $items = [];

        foreach (scandir($path) as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $name;

            $items[] = new DirectoryItemVo(
                name: $name,
                path: $fullPath,
                isFile: is_file($fullPath),
                isDirectory: is_dir($fullPath),
            );
        }

        return BaseResponse::success(
            $response,
            $items
        );
    }


    #[OA\Post(
        path: "/api/rain/createDirectory",
        operationId: "createDirectory",
        summary: "创建目录",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function createDirectory(
        Request $request,
        Response $response
    ): Response {
        $path = dirname(__DIR__, 2) . '/runtime/demo';

        $success = is_dir($path);

        if (!$success) {
            $success = mkdir(
                directory: $path,
                permissions: 0777,
                recursive: true
            );
        }

        return BaseResponse::success(
            $response,
            new FileOperationVo(
                path: $path,
                success: $success,
            )
        );
    }


    #[OA\Post(
        path: "/api/rain/copyFile",
        operationId: "copyFile",
        summary: "复制文件",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function copyFile(
        Request $request,
        Response $response
    ): Response {
        $source = dirname(__DIR__, 2) . '/composer.json';
        $target = dirname(__DIR__, 2) . '/logs/runtime/composer-copy.json';

        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        $success = copy($source, $target);

        return BaseResponse::success(
            $response,
            new FileOperationVo(
                path: $target,
                success: $success,
            )
        );
    }


    #[OA\Delete(
        path: "/api/rain/deleteFile",
        operationId: "deleteFile",
        summary: "删除文件",
        tags: ["File"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function deleteFile(
        Request $request,
        Response $response
    ): Response {
        $path = dirname(__DIR__, 2) . '/runtime/composer-copy.json';

        $success = !file_exists($path);

        if (!$success) {
            $success = unlink($path);
        }

        return BaseResponse::success(
            $response,
            new FileOperationVo(
                path: $path,
                success: $success,
            )
        );
    }
}