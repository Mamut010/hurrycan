<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\IsOptionalBase;
use App\Support\Optional\Optional;
use Attribute;

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
