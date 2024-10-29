<?php
namespace App\Core\Global;

use App\Core\Http\Request\RequestGlobalCollection;

interface GlobalCollection extends RequestGlobalCollection
{
    function get(): array;
    function post(): array;
}
