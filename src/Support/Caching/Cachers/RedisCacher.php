<?php
namespace App\Support\Caching\Cachers;

use App\Settings\RedisSetting;
use App\Support\Caching\Exceptions\CacheException;
use App\Support\Caching\ExpirySupportCacher;

class RedisCacher implements ExpirySupportCacher
{
    private const SUPPORTER_REDIS_TYPE = 'STRING';
    private const EXP_KEY_NO_EXPIRY = -1;
    private const EXP_KEY_NOT_EXIST = -2;

    private \Redis $redis;

    public function __construct(string $redisHost, int $redisPort) {
        $this->redis = new \Redis([
            'host' => $redisHost,
            'port' => $redisPort,
            'connectTimeout' => RedisSetting::CONNECTION_TIMEOUT,
            'backoff' => RedisSetting::BACKOFF,
        ]);
    }

    #[\Override]
    public function contains(string $key): bool {
        try {
            return $this->redis->exists($key);
        }
        catch (\RedisException $e) {
            throw new CacheException($e->getMessage());
        }
    }

    #[\Override]
    public function missing(string $key): bool {
        return !$this->contains($key);
    }

    #[\Override]
    public function set(string $key, string $value, ?int $ttlMs = null): bool {
        return $this->setCases($key, $value, $ttlMs);
    }

    #[\Override]
    public function setIfAbsent(string $key, string $value, ?int $ttlMs = null): bool {
        return $this->setCases($key, $value, $ttlMs, ['NX']);
    }

    #[\Override]
    public function setIfExist(string $key, string $value, ?int $ttlMs = null): bool {
        return $this->setCases($key, $value, $ttlMs, ['XX']);
    }

    private function setCases(string $key, string $value, ?int $ttlMs, array $options = []) {
        try {
            $finalOptions = $options;
            if ($ttlMs === null) {
                $finalOptions['KEEPTTL'] = true;
            }
            elseif ($ttlMs > 0) {
                $finalOptions['PX'] = $ttlMs;
            }
            else {
                $finalOptions = !empty($finalOptions) ? $finalOptions : null;
            }
            return $this->redis->set($key, $value, $finalOptions) !== false;
        }
        catch (\RedisException $e) {
            throw new CacheException($e->getMessage());
        }
    }

    #[\Override]
    public function get(string $key): string|false {
        try {
            $result = $this->redis->get($key);
            return static::valueOrFalse($result);
        }
        catch (\RedisException $e) {
            throw new CacheException($e->getMessage());
        }
    }

    #[\Override]
    public function getSetExp(string $key, int $ttlMs = null): string|false {
        try {
            $options = $ttlMs !== null ? ['PX' => $ttlMs] : ['PERSIST'];
            $result = $this->redis->getEx($key, $options);
            return static::valueOrFalse($result);
        }
        catch (\RedisException $e) {
            throw new CacheException($e->getMessage());
        }
    }

    #[\Override]
    public function getExpireTime(string $key): int|false|null {
        $exp = $this->redis->expiretime($key);
        if ($exp === static::EXP_KEY_NO_EXPIRY) {
            return null;
        }
        elseif ($exp === static::EXP_KEY_NOT_EXIST) {
            return false;
        }
        else {
            return $exp;
        }
    }

    #[\Override]
    public function persist(string $key): bool {
        try {
            return $this->redis->persist($key) !== false;
        }
        catch (\RedisException $e) {
            throw new CacheException($e->getMessage());
        }
    }

    #[\Override]
    public function extract(string $key): string|false {
        $result = $this->redis->getDel($key);
        return static::valueOrFalse($result);
    }

    #[\Override]
    public function delete(string|array $key, string ...$otherKeys): int|false {
        try {
            return $this->redis->unlink($key, ...$otherKeys);
        }
        catch (\RedisException $e) {
            throw new CacheException($e->getMessage());
        }
    }

    #[\Override]
    public function keys(?string $pattern = null): \Iterator {
        try {
            $it = null;
            while ($it !== 0) {
                /**
                 * @var string[]|false|\Redis
                 */
                $result = $this->redis->scan($it, $pattern, type: static::SUPPORTER_REDIS_TYPE);
                if (is_array($result)) {
                    foreach ($result as $key) {
                        yield $key;
                    }
                }
            }
        }
        catch (\RedisException $e) {
            throw new CacheException($e->getMessage());
        }
    }

    #[\Override]
    public function increment(string $key, int|float $by = 1): int|float|false {
        try {
            if (is_int($by)) {
                return $this->redis->incrBy($key, $by);
            }
            else {
                return $this->redis->incrByFloat($key, $by);
            }
        }
        catch (\RedisException $e) {
            return false;
        }
    }

    #[\Override]
    public function decrement(string $key, int|float $by = 1): int|float|false {
        try {
            if (is_int($by)) {
                return $this->redis->decrBy($key, $by);
            }
            else {
                return $this->redis->incrByFloat($key, -$by);
            }
        }
        catch (\RedisException $e) {
            return false;
        }
    }

    private static function valueOrFalse(mixed $result) {
        return $result !== false && !$result instanceof \Redis ? $result : false;
    }
}
