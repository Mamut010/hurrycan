<?php
namespace App\Core\Di\Syntax;

use App\Core\Di\Contracts\BindingInSyntax;
use App\Core\Di\Contracts\BindingNoopSyntax;
use App\Core\Di\Contracts\BindingToSyntax;
use App\Core\Di\Contracts\DiBindingContext;

class DefaultBindingToSyntax implements BindingToSyntax
{
    private DiBindingContext $context;
    private string $id;

    public function __construct(DiBindingContext $context, string $id)
    {
        $this->context = $context;
        $this->id = $id;
    }

    #[\Override]
    public function to(string $class): BindingInSyntax {
        return new ClassBoundSyntax($this->context, $this->id, $class);
    }

    #[\Override]
    public function toSelf(): BindingInSyntax {
        return new SelfBoundSyntax($this->context, $this->id);
    }

    #[\Override]
    public function toFactory(callable $factory): BindingInSyntax {
        return new FactoryBoundSyntax($this->context, $this->id, $factory);
    }

    #[\Override]
    public function toConstant(mixed $constant): BindingNoopSyntax {
        return new ConstantBoundSyntax($this->context, $this->id, $constant);
    }
}
