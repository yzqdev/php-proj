<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\DTO;

/**
 * 用户登录 DTO
 */
readonly class UserLoginDto
{
    public function __construct(
        public string $username,
        public string $password,
    ) {
    }
}
