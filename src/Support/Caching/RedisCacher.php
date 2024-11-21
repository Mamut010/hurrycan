<?php
namespace App\Support\Caching;

use Redis;

class RedisCacher
{
    private Redis $redis;

    public function __construct() {
        $this->redis = new Redis([
            'host' => 'redis',
            'port' => 6379,
            'connectTimeout' => 2.5,
            'ssl' => ['verify_peer' => false],
            'backoff' => [
                'algorithm' => Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
                'base' => 500,
                'cap' => 750,
            ],
        ]);
    }

    public function set(string $key, string $value, int $ttlMs = null): bool {
        $options = $ttlMs !== null ? ['px' => $ttlMs] : null;
        $result = $this->redis->set($key, $value, $options);
        return $result !== false;
    }

    public function setIfAbsent(string $key, string $value, int $ttlMs = null): bool {
        $options = $ttlMs !== null ? ['nx', 'px' => $ttlMs] : ['nx'];
        $result = $this->redis->set($key, $value, $options);
        return $result !== false;
    }

    public function setIfExist(string $key, string $value, int $ttlMs = null): bool {
        $options = $ttlMs !== null ? ['xx', 'px' => $ttlMs] : ['xx'];
        $result = $this->redis->set($key, $value, $options);
        return $result !== false;
    }

    public function get(string $key): string|false {
        $result = $this->redis->get($key);
        return static::valueOrFalse($result);
    }

    public function getSetExp(string $key, int $ttlMs = null): string|false {
        $options = $ttlMs !== null ? ['px' => $ttlMs] : ['PERSIST'];
        $result = $this->redis->getEx($key, $options);
        return static::valueOrFalse($result);
    }

    public function extract(string $key): string|false {
        $result = $this->redis->getDel($key);
        return static::valueOrFalse($result);
    }

    public function delete(string|array $key, string ...$otherKeys): int|false {
        return $this->redis->unlink($key, ...$otherKeys);
    }

    private static function valueOrFalse(mixed $result) {
        return $result !== false && !$result instanceof Redis ? $result : false;
    }
}
