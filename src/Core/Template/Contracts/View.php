<?php
namespace App\Core\Template\Contracts;

interface View
{
    function getName(): string;
    function with(string $key, mixed $value): self;
}
