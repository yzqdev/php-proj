<?php

declare(strict_types=1);

namespace Yzqde\Fox\Vo;

final readonly class DirectoryItemVo
{
    public function __construct(
        public string $name,
        public string $path,
        public bool $isFile,
        public bool $isDirectory,
    ) {
    }
}
