<?php
namespace App\Core\Template\Contracts;

interface TemplateParser
{
    function parse(string $template): string;
}
