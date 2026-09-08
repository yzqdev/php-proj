<?php

declare(strict_types=1);

namespace Yzqde\Playground\Service;

use Psr\Http\Message\UploadedFileInterface;

/**
 * 从 PSR-7 UploadedFile 提取出的待校验数据
 *
 * PSR-7 不暴露临时文件路径,内容通过流读出后统一校验,
 * 这样 Service 不依赖任何具体实现(测试时也便于构造)。
 */
final readonly class UploadedFile
{
    public function __construct(
        public int $error,
        public int $size,
        public ?string $clientName,
        public string $content,
    ) {
    }

    public static function fromPsr7(UploadedFileInterface $file): self
    {
        $stream = $file->getStream();
        $size = $file->getSize() ?? $stream->getSize() ?? 0;
        return new self(
            error: $file->getError(),
            size: $size,
            clientName: $file->getClientFilename(),
            content: (string)$stream->getContents(),
        );
    }
}
