<?php

declare(strict_types=1);

namespace Yzqde\Fox\Vo;

final readonly class FileVo
{
    public function __construct(
        public string $path,
        public string $description,
    ) {
    }
}

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

final readonly class FileContentVo
{
    public function __construct(
        public string $path,
        public string $content,
    ) {
    }
}

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

final readonly class FileOperationVo
{
    public function __construct(
        public string $path,
        public bool $success,
    ) {
    }
}