<?php
namespace App\Core\Routing\Contracts;

interface WhereableRoute {
    function where(string $param, string $pattern): self;
    function whereNumber(string $param): self;
    function whereAlpha(string $param): self;
    function whereAlphaNumeric(string $param): self;
    function whereIn(string $param, array $values): self;
}
