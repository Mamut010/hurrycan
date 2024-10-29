<?php
namespace App\Core\Di;

use App\Core\Di\Contracts\ReadonlyDiContainer;

final class InjectionContext
{
    public function __construct(
        private string $id,
        private ReadonlyDiContainer $container
        ) {
    }

    public function id(): string {
        return $this->id;
    }

    public function container(): ReadonlyDiContainer {
        return $this->container;
    }
}
