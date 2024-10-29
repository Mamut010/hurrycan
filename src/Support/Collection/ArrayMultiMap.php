<?php
namespace App\Support\Collection;

use App\Utils\Arrays;

class ArrayMultiMap implements MultiMap
{
    /**
    * @var array<string,array>
    */
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
    public function containsValue(mixed $value, string $key): bool {
        return $this->contains($key) && in_array($value, $this->storage[$key]);
    }

    #[\Override]
    public function get(string $key): array|false {
        return $this->contains($key) ? $this->storage[$key] : false;
    }

    #[\Override]
    public function put(string $key, mixed $value): void {
        $group = $this->getOrCreateGroup($key);
        $group[] = $value;
    }

    #[\Override]
    public function putIfAbsent(string $key, mixed $value): void {
        $group = $this->getOrCreateGroup($key);
        if (!in_array($value, $group, true)) {
            $this->storage[$key][] = $value;
        }
    }

    private function &getOrCreateGroup(string $key): array {
        if ($this->contains($key)) {
            $group = &$this->storage[$key];
        }
        else {
            $group = [];
            $this->storage[$key] = &$group;
        }
        return $group;
    }

    #[\Override]
    public function set(string $key, mixed $values): void {
        $this->storage[$key] = Arrays::asArray($values);
    }

    #[\Override]
    public function remove(string $key): array|false {
        if (!$this->contains($key)) {
            return false;
        }
        
        $group = $this->storage[$key];
        unset($this->storage[$key]);
        return $group;
    }

    public function removeValue(mixed $value, string $key): bool {
        if (!$this->contains($key)) {
            return false;
        }

        $group = &$this->storage[$key];
        $index = array_search($value, $group, true);
        if ($index === false) {
            return false;
        }

        if (count($group) === 1) {
            unset($this->storage[$key]);
        }
        else {
            array_splice($group, $index, 1);
        }
        return true;
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
    public function values(): array
    {
        return array_values($this->storage);
    }
}
