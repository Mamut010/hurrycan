<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\IsOptionalBase;
use App\Support\Optional\Optional;
use Attribute;

/**
 * Mark a property as optional. If the property is missing in the subject, the model's property
 * will be assigned a specified default value.
 * Useful in cases where default values cannot be specified directly in the model due to constant expression limitation.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsOptionalDefaulted extends IsOptionalBase
{
    public function __construct(private readonly mixed $default) {
        
    }

    #[\Override]
    public function getDefaultValue(): Optional {
        return Optional::of($this->default);
    }
}
