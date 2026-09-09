<?php

namespace Yzqde\Fox\Util;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Cache
{
    public function __construct(
        public int $ttl = 60
    )
    {
    }
}