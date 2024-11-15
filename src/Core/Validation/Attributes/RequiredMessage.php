<?php
namespace App\Core\Validation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class RequiredMessage
{
    public const PLACEHOLDER_FIELD_NAME = '{property}';

    public function __construct(private readonly string $message) {
        
    }

    public function getMessage(string $propName): string {
        return str_replace(static::PLACEHOLDER_FIELD_NAME, $propName, $this->message);
    }
}
