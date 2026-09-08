<?php

namespace App\Controller;

use App\Config\FileConst;
use App\Response\BaseResponse;
use App\Service\FileService;
use Monolog\Logger;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PathController
{
    public function __construct(

        private readonly Logger $logger,
    )
    {
    }


    /**
     * GET /api/path/getpath — 返回配置目录的绝对路径
     *
     * @param Request  $request  PSR-7 HTTP 请求对象
     * @param Response $response PSR-7 HTTP 响应对象
     *
     * @return Response JSON 响应，data 为配置目录绝对路径
     */
    #[OA\Get(path: '/api/path/getpath', summary: '获取配置目录绝对路径',   tags: ['其它'])]
    #[OA\Response(
        response: 200,
        description: '获取成功',

        content: new OA\JsonContent(
            required: ['code', 'message', 'data'],
            properties: [
                new OA\Property(property: 'code', type: 'integer', example: 0),
                new OA\Property(property: 'message', type: 'string', example: '获取成功'),
                new OA\Property(property: 'data', type: 'string', example: 'F:\\\\PhpStormProjects\\\\php-proj\\\\php-tutor\\\\codeforge\\\\config'),
            ],
        ),
    )]
    public function getPath(Request $request, Response $response): Response
    {
        $this->logger->info(realpath(FileConst::ROOT_DIR));

        return BaseResponse::success($response, realpath(FileConst::CONFIG_DIR));
    }
}