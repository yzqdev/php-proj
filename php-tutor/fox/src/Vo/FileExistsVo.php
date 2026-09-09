<?php

declare(strict_types=1);

namespace Yzqde\Fox\Vo;

final readonly class FileExistsVo
{
    public function __construct(
        public string $path,
        public bool $exists,
        public bool $isFile,
        public bool $isDirectory,
    ) {
    }
}
