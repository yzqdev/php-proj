<?php

declare(strict_types=1);

namespace Yzqde\Fox\Vo;

final readonly class FileContentVo
{
    public function __construct(
        public string $path,
        public string $content,
    ) {
    }
}
