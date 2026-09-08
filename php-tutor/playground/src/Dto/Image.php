<?php

declare(strict_types=1);

namespace Yzqde\Playground\Dto;

use OpenApi\Attributes as OA;

/**
 * 已入库图片的只读视图:Service 产出,Controller/模板消费
 *
 * OA\Schema 属性把 toArray() 的输出结构登记为 OpenAPI 组件 schema,供接口文档引用
 */
#[OA\Schema(
    schema: 'Image',
    title: '图片',
    description: '已入库图片的视图;url 为站内相对外链,拼接域名后即为可直接引用的图片地址',
)]
final readonly class Image
{
    public function __construct(
        #[OA\Property(
            property: 'name',
            description: '随机存储名,32 位十六进制加扩展名,不可猜测也不可复用',
            type: 'string',
            example: '5803ef05b9b3fba37f60a78cb180a273.png',
        )]
        public string $name,
        #[OA\Property(
            property: 'original',
            description: '上传时的原始文件名,仅保存在旁车元数据中',
            type: 'string',
            example: '屏幕截图(10).png',
        )]
        public string $original,
        #[OA\Property(
            property: 'size',
            description: '文件字节数',
            type: 'integer',
            format: 'int64',
            example: 3766382,
        )]
        public int $size,
        #[OA\Property(
            property: 'time',
            description: '上传时间,Unix 秒',
            type: 'integer',
            format: 'int64',
            example: 1788809509,
        )]
        public int $time,
    ) {
    }

    /**
     * 站内相对外链;JS 端拼接域名后即为可直接引用的图片地址
     */
    #[OA\Property(
        property: 'url',
        description: '站内相对外链,拼接域名后即为可直接引用的图片地址',
        type: 'string',
        example: '/i/5803ef05b9b3fba37f60a78cb180a273.png',
    )]
    public function url(): string
    {
        return '/i/' . rawurlencode($this->name);
    }

    /**
     * @return array{name: string, original: string, size: int, time: int, url: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'original' => $this->original,
            'size' => $this->size,
            'time' => $this->time,
            'url' => $this->url(),
        ];
    }
}
