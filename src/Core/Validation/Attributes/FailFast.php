<?php
namespace App\Core\Validation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class FailFast
{

}
