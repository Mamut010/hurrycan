<?php
namespace App\Core\Routing\Contracts;

interface RouteGroup extends Route
{
    function setController(?string $controller);

    /**
     * @return Route[]
     */
    function getChildren(): array;

    /**
     * @param Route[] $children
     */
    function addChildren(array $children): void;
    function addChild(Route $child): void;
    function removeChild(Route $child): void;
}
