<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\IsOptionalBase;
use App\Support\Optional\Optional;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsOptional extends IsOptionalBase
{
    #[\Override]
    public function getDefaultValue(): Optional {
        return Optional::empty();
    }
}
