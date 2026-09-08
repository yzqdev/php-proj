<?php

declare(strict_types=1);

namespace Yzqde\Playground\Exception;

/**
 * 图床业务异常基类:消息面向用户展示,不允许携带堆栈、路径等内部信息
 */
abstract class ImageHostException extends ApiException
{
}
