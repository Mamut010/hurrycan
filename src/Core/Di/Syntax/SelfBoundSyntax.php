<?php
namespace App\Core\Di\Syntax;

use App\Core\Di\Contracts\ClassBindingContext;

class SelfBoundSyntax extends ClassBoundSyntax
{
    public function __construct(ClassBindingContext $context, string $id)
    {
        parent::__construct($context, $id, $id);
    }
}
