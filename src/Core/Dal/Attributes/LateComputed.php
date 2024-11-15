<?php
namespace App\Core\Dal\Attributes;

use Attribute;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class LateComputed extends ComputedBase
{
    /**
     * Late computation of a property by invoke the callback with the provided instance and property.
     *
     * @param object $instance The instance on which to execute the callback
     * @param \ReflectionProperty $prop The computed property
     * @return mixed The result of the callback.
     */
    public function compute(object $instance, \ReflectionProperty $prop): mixed {
        return $this->invokeCallback($instance, $prop);
    }
}
