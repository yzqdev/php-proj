<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\DTO;

/**
 * 用户注册 DTO
 */
readonly class UserRegisterDto
{
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
    ) {
    }
}
