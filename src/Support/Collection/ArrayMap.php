<?php
namespace App\Support\Collection;

use App\Utils\Arrays;

class ArrayMap implements Map
{
    private array $storage;

    public function __construct(?array $data = null) {
        $this->storage = [];
        if ($data) {
            foreach ($data as $key => $value) {
                $this->put($key, $value);
            }
        }
    }

    #[\Override]
    public function size(): int {
        return count($this->storage);
    }

    #[\Override]
    public function isEmpty(): bool {
        return empty($this->storage);
    }

    #[\Override]
    public function clear(): void {
        $this->storage = [];
    }

    #[\Override]
    public function contains(string $key): bool {
        return array_key_exists($key, $this->storage);
    }

    #[\Override]
    public function containsValue(mixed $value): bool {
        return array_search($value, $this->storage, true) !== false;
    }

    #[\Override]
    public function get(string $key): mixed {
        if ($this->contains($key)) {
            throw new \OutOfBoundsException("Attempt to access a non-existent key [$key] in a map");
        }
        return $this->storage[$key];
    }

    #[\Override]
    public function getOrDefault(string $key, mixed $default): mixed {
        return Arrays::getOrDefaultExists($this->storage, $key, $default);
    }

    #[\Override]
    public function put(string $key, mixed $value): void {
        $this->storage[$key] = $value;
    }

    #[\Override]
    public function putIfAbsent(string $key, mixed $value): void {
        if (!$this->contains($key)) {
            $this->put($key, $value);
        }
    }

    #[\Override]
    public function remove(string $key): mixed {
        if (!$this->contains($key)) {
            return null;
        }
        
        $value = $this->storage[$key];
        unset($this->storage[$key]);
        return $value;
    }

    #[\Override]
    public function toArray(): array {
        return $this->storage;
    }
    
    #[\Override]
    public function iter(): \Iterator {
        return new \ArrayIterator($this->storage);
    }

    #[\Override]
    public function keys(): array {
        return array_keys($this->storage);
    }

    #[\Override]
    public function values(): array {
        return array_values($this->storage);
    }
}
