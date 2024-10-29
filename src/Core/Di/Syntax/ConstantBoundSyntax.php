<?php
namespace App\Core\Di\Syntax;

use App\Core\Di\Contracts\BindingNoopSyntax;
use App\Core\Di\Contracts\ConstantBindingContext;

class ConstantBoundSyntax implements BindingNoopSyntax
{
    private ConstantBindingContext $context;
    private string $id;
    private mixed $constant;

    public function __construct(ConstantBindingContext $context, string $id, mixed $constant)
    {
        $this->context = $context;
        $this->id = $id;
        $this->constant = $constant;
        $this->bind();
    }

    private function bind() {
        $this->context->bindToConstant($this->id, $this->constant);
    }
}
