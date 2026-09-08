<?php

declare(strict_types=1);

namespace Yzqde\Playground\Exception;

use RuntimeException;

/**
 * 面向用户的业务异常基类:消息可直接回显给客户端,不允许携带堆栈、SQL、绝对路径等内部信息
 *
 * JsonErrorRenderer 按本基类判断是否展示异常原文;新增需要用户可读的错误时继承它。
 *
 * 不继承 Slim\Exception\HttpException(它强制要求构造时传入 ServerRequestInterface,
 * 业务层抛出点拿不到),因此 Slim 的错误处理器一律按 500 处理本基类及其子类。
 * 即便显式传入状态码也不会生效,需要区分 4xx 时须在控制器内自行捕获并返回,
 * 见 README「已知问题」。
 */
abstract class ApiException extends RuntimeException
{
}
