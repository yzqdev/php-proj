<?php

declare(strict_types=1);

namespace Yzqde\Fox\Util;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    private string $key;
    private mixed $value;
    private bool $hit;
    private ?DateTimeImmutable $expiresAt = null;

    public function __construct(string $key, mixed $value, bool $hit)
    {
        $this->key = $key;
        $this->value = $value;
        $this->hit = $hit;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->hit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->hit = true;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiresAt = $expiration;

        return $this;
    }

    public function expiresAfter(DateInterval|int|null $time = null): static
    {
        if ($time === null) {
            $this->expiresAt = null;
        } elseif ($time instanceof DateInterval) {
            $this->expiresAt = new DateTimeImmutable()->add($time);
        } else {
            $this->expiresAt = new DateTimeImmutable()->modify("+{$time} seconds");
        }

        return $this;
    }
}
