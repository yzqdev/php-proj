<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

use JsonException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HTTP 请求体助手。
 *
 * 用于从 PSR-7 Request 中读取并解析 JSON 请求体。
 */
final class RequestBody
{
    private function __construct()
    {
    }

    /**
     * 获取 JSON 请求体。
     *
     * 优先使用 PSR-7 已解析的 body。
     * 如果没有解析结果，则从原始 body 中解析 JSON。
     *
     * @return array<string, mixed>
     */
    public static function json(ServerRequestInterface $request): array
    {
        $parsed = $request->getParsedBody();

        if (is_array($parsed)) {
            return $parsed;
        }

        if (is_object($parsed)) {
            return get_object_vars($parsed);
        }

        return self::decode((string) $request->getBody());
    }

    /**
     * 将 JSON 请求体转换为指定实体。
     *
     * 例如：
     *
     *     $user = RequestBody::entity($request, CreateUserRequest::class);
     *
     * @template T of object
     *
     * @param class-string<T> $class
     * @return T
     */
    public static function entity(
        ServerRequestInterface $request,
        string $class,
    ): object {
        $data = self::json($request);

        return self::map($class, $data);
    }

    /**
     * 将数组映射为实体。
     *
     * @template T of object
     *
     * @param class-string<T> $class
     * @param array<string, mixed> $data
     * @return T
     */
    private static function map(string $class, array $data): object
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            $entity = $reflection->newInstance();

            foreach ($data as $property => $value) {
                if ($reflection->hasProperty($property)) {
                    $reflection->getProperty($property)->setValue($entity, $value);
                }
            }

            return $entity;
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $data)) {
                $arguments[] = $data[$name];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            $arguments[] = null;
        }

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * 解析 JSON 字符串。
     *
     * @return array<string, mixed>
     */
    private static function decode(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        try {
            $data = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException) {
            return [];
        }

        return is_array($data) ? $data : [];
    }
}