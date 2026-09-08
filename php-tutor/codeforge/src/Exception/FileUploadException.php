<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * 文件上传业务异常
 *
 * 用于表达「上传被拒绝」这类可预期的业务错误：文件过大、类型不在白名单、
 * 内容与扩展名不匹配、内容为空等。由 FileService 抛出，FileController 捕获
 * 后统一转换为 422 JSON 响应，无需在控制器里散落判断。
 */
final class FileUploadException extends RuntimeException
{
}
