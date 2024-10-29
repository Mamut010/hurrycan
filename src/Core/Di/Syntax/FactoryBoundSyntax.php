<?php
namespace App\Core\Di\Syntax;

use App\Core\Di\Contracts\BindingInSyntax;
use App\Core\Di\Contracts\FactoryBindingContext;

class FactoryBoundSyntax implements BindingInSyntax
{
    private FactoryBindingContext $context;
    private string $id;
    private \Closure $factory;

    public function __construct(FactoryBindingContext $context, string $id, callable $factory)
    {
        $this->context = $context;
        $this->id = $id;
        $this->factory = \Closure::fromCallable($factory);
        $this->inTransientScope();
    }

    #[\Override]
    public function inTransientScope(): void {
        $this->context->bindToFactory($this->id, $this->factory);
        $this->context->removeSingletonScoped($this->id);
    }

    #[\Override]
    public function inSingletonScope(): void {
        $this->context->bindToFactory($this->id, $this->factory);
        $this->context->addSingletonScoped($this->id);
    }
}
