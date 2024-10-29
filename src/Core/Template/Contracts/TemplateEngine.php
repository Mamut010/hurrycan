<?php
namespace App\Core\Template\Contracts;

interface TemplateEngine
{
    function share(string $key, string $value): self;
    function view(string $viewName, ?array $context = null): View;
}
