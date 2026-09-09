<?php

declare(strict_types=1);

namespace Yzqde\Fox\Vo;

final readonly class FileInfoVo
{
    public function __construct(
        public string $path,
        public bool $exists,
        public int $size,
        public string $modifiedTime,
        public string $permissions,
        public string $type,
    ) {
    }
}
