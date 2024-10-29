<?php
namespace App\Support\Csrf;

interface CsrfHandler
{
    function generate(string $data): string;
    function validate(string $csrfToken, string $data): bool;
}
