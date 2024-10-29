<?php
namespace App\Core\Di\Syntax;

use App\Core\Di\Contracts\BindingInSyntax;
use App\Core\Di\Contracts\ClassBindingContext;

class ClassBoundSyntax implements BindingInSyntax
{
    private ClassBindingContext $context;
    private string $id;
    private string $class;

    public function __construct(ClassBindingContext $context, string $id, string $class)
    {
        $this->context = $context;
        $this->id = $id;
        $this->class = $class;
        $this->inTransientScope();
    }

    #[\Override]
    public function inTransientScope(): void {
        $this->context->bindToClass($this->id, $this->class);
        $this->context->removeSingletonScoped($this->id);
    }

    #[\Override]
    public function inSingletonScope(): void {
        $this->context->bindToClass($this->id, $this->class);
        $this->context->addSingletonScoped($this->id);
    }
}
