<?php
namespace App\Core\Shared;

use Attribute;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class Computed extends ComputedBase
{
    /**
     * Eager computation of a property by invoking the callback with the provided instance,
     * a supplied value and property.
     *
     * @param object $instance The instance on which to execute the callback
     * @param mixed $value The supplied value
     * @param \ReflectionProperty $prop The computed property
     * @return mixed The result of the callback.
     */
    public function compute(object $instance, mixed $value, \ReflectionProperty $prop): mixed {
        return $this->invokeCallback($instance, $value, $prop);
    }
}
