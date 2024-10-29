<?php
namespace App\Core\Http\Request;

interface RequestGlobalCollection
{
    function server(): array;
    function body(): array;
    function files(): array;
    function cookie(): array;
    function headers(): array;
}
