<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\IsOptionalBase;
use App\Support\Optional\Optional;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class OptionalModel extends IsOptionalBase
{
    #[\Override]
    public function getDefaultValue(): Optional {
        return Optional::empty();
    }
}
