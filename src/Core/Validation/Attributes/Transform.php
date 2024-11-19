<?php
namespace App\Core\Validation\Attributes;

use App\Utils\Reflections;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Transform
{
    /**
     * @param string|array $callback A callable string or array
     */
    public function __construct(private readonly string|array $callback) {
        
    }

    public function invoke(object $instance, mixed $value): mixed {
        return Reflections::invokeMethodOrCallNoInstance($instance, $this->callback, $value);
    }
}
