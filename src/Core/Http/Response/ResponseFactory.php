<?php
namespace App\Core\Http\Response;

interface ResponseFactory
{
    function make(mixed $data = null): Response;
    function json(mixed $data): Response;
    function view(string $viewName, ?array $context = null): Response;
    function err(int $statusCode, ?string $message = null): Response;
    function errJson(int $statusCode, mixed $data): Response;
    function errView(int $statusCode, string $viewName, ?array $context = null): Response;
    function file(string $filename): Response;
    function download(string $filename, ?string $downloadedFilename = null): Response;
    function fileContent(string $fileContent): Response;
    function downloadContent(string $fileContent, ?string $downloadedFilename = null): Response;
}
