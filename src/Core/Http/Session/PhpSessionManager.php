<?php
namespace App\Core\Http\Session;

use App\Core\Http\Session\SessionManager;
use App\Utils\Arrays;
use App\Utils\Sessions;

class PhpSessionManager implements SessionManager
{
    private function wrap(callable $callback, mixed $defaultOrCallback = null) {
        if (Sessions::isStarted()) {
            return call_user_func($callback);
        }
        elseif (is_callable($defaultOrCallback)) {
            return call_user_func($defaultOrCallback);
        }
        else {
            return $defaultOrCallback;
        }
    }

    #[\Override]
    public function id(): string {
        return $this->wrap(fn() => session_id(), '');
    }

    #[\Override]
    public function has(string $key): bool {
        $default = false;
        return $this->wrap(fn() =>
             Arrays::getOrDefault($_SESSION, $key, $default),
             $default
        );
    }

    #[\Override]
    public function exists(string $key): bool {
        $default = false;
        return $this->wrap(fn() =>
             Arrays::getOrDefaultExists($_SESSION, $key, $default),
             $default
        );
    }

    #[\Override]
    public function missing(string $key): bool {
        return !$this->exists($key);
    }

    #[\Override]
    public function all(): array {
        return $this->wrap(fn() => $_SESSION, []);
    }

    #[\Override]
    public function get(string $key, mixed $defaultOrCallback = null): mixed {
        if (Sessions::isStarted() && array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        elseif (is_callable($defaultOrCallback)) {
            return call_user_func($defaultOrCallback);
        }
        else {
            return $defaultOrCallback;
        }
    }

    #[\Override]
    public function put(string $key, mixed $value): void {
        $this->wrap(fn() => $_SESSION[$key] = $value);
    }

    #[\Override]
    public function pull(string $key, mixed $defaultOrCallback = null): mixed {
        if (Sessions::isStarted() && array_key_exists($key, $_SESSION)) {
            $result = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $result;
        }
        elseif (is_callable($defaultOrCallback)) {
            return call_user_func($defaultOrCallback);
        }
        else {
            return $defaultOrCallback;
        }
    }

    #[\Override]
    public function forget(string|array $key): void {
        $this->wrap(function () use ($key) {
            $keys = Arrays::asArray($key);
            foreach ($keys as $key) {
                unset($_SESSION[$key]);
            }
        });
    }

    #[\Override]
    public function flush(): void {
        $this->wrap(fn() => array_splice($_SESSION, 0));
    }
}
