<?php
namespace App\Core\Http\Response;

interface ResponseFactory
{
    function make(mixed $data = null): Response;
    function json(mixed $data): Response;
    function view(string $viewName, ?array $context = null): Response;
    function err(int $statusCode, ?string $message = null): Response;
    function errView(int $statusCode, string $viewName, ?array $context = null): Response;
}
