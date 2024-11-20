<?php
namespace App\Core\Validation\Attributes;

use Attribute;

/**
 * Set a default message to use if a property is missing.
 * It supports the placeholder {property} to be used as the property's name.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class RequiredMessage
{
    public const PLACEHOLDER_PROPERTY_NAME = '{property}';

    public function __construct(private readonly string $message) {
        
    }

    public function getMessage(string $propName): string {
        return str_replace(static::PLACEHOLDER_PROPERTY_NAME, $propName, $this->message);
    }
}
