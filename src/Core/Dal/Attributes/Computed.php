<?php
namespace App\Core\Dal\Attributes;

use App\Utils\Reflections;
use Attribute;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class Computed
{
    /**
     * @param string $callback A callable string
     */
    public function __construct(private readonly string $callback) {
        
    }

    /**
     * Invoke the callback with the provided instance.
     *
     * @param object $instance The instance on which to execute the callback.
     * @return mixed The result of the callback.
     */
    public function compute(object $instance): mixed {
        if (is_string($this->callback) && method_exists($instance, $this->callback)) {
            return Reflections::invokeMethod($instance, $this->callback);
        }

        if (is_callable($this->callback)) {
            return call_user_func($this->callback, $instance);
        }

        throw new \InvalidArgumentException("Invalid callback provided.");
    }
}
