<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider;

class StructuredDataContainer
{
    protected array $data = [];

    public function getStructuredData(): array
    {
        return $this->data;
    }

    public function add(array $data, string $node): array
    {
        foreach ($data as $key => $value) {
            $this->addKey($node, $key, $value);
        }

        return $this->data;
    }

    public function addKey(string $node, string|int $key, $value) // phpcs:ignore
    {
        $this->data[$node][$key] = $value;

        return $this->data[$node][$key];
    }

    public function removeKey(string $node, string|int $key): void
    {
        if (!isset($this->data[$node]) || !isset($this->data[$node][$key])) {
            return;
        }

        unset($this->data[$node][$key]);
    }

    public function __destruct()
    {
        $this->data = [];
    }
}
