<?php

declare(strict_types=1);

namespace Yzqde\Fox\Services;

class Store
{
    private array $data = [];
    private int $nextId = 1;

    public function all(): array
    {
        return array_values($this->data);
    }

    public function find(int $id): ?array
    {
        return $this->data[$id] ?? null;
    }

    public function create(array $data): array
    {
        $data['id'] = $this->nextId++;
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->data[$data['id']] = $data;
        return $data;
    }

    public function update(int $id, array $data): ?array
    {
        if (!isset($this->data[$id])) {
            return null;
        }
        $data['id'] = $id;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->data[$id] = array_merge($this->data[$id], $data);
        return $this->data[$id];
    }

    public function delete(int $id): bool
    {
        if (!isset($this->data[$id])) {
            return false;
        }
        unset($this->data[$id]);
        return true;
    }
}
