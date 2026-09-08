<?php

declare(strict_types=1);

namespace Yzqde\Playground\Exception;

/**
 * 上传内容不合法(类型、大小、格式校验未通过),对应 HTTP 400
 */
final class InvalidUploadException extends ImageHostException
{
}
