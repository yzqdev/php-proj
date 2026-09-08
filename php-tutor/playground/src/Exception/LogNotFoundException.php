<?php

declare(strict_types=1);

namespace Yzqde\Playground\Exception;

/**
 * 请求的日志文件不存在或文件名不合法
 *
 * 语义上应为 404,但受 ApiException 的约束当前由错误中间件统一返回 500,见 README「已知问题」。
 */
final class LogNotFoundException extends ApiException
{
}
