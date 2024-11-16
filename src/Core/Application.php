<?php
namespace App\Core;

use App\Core\Di\Contracts\ReadonlyDiContainer;

interface Application
{
    function container(): ReadonlyDiContainer;
    function run(?callable $fallback = null): void;
}
