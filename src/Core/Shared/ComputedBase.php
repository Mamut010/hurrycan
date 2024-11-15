<?php
namespace App\Core\Shared;

use App\Utils\Reflections;

class ComputedBase
{
    /**
     * @param string $callback A callable string
     */
    public function __construct(private readonly string $callback) {
        
    }

    final protected function invokeCallback(object $instance, mixed ...$args): mixed {
        if (is_string($this->callback) && method_exists($instance, $this->callback)) {
            return Reflections::invokeMethod($instance, $this->callback, ...$args);
        }

        if (is_callable($this->callback)) {
            return call_user_func($this->callback, $instance, ...$args);
        }

        throw new \InvalidArgumentException("Invalid callback provided.");
    }
}
