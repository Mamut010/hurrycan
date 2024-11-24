<?php
namespace App\Core\Http\Response\Impl;

use App\Core\Http\Cookie\CookieQueue;
use App\Core\Http\Response\Helpers\DownloadContentDisposition;
use App\Core\Http\Response\Helpers\InlineContentDisposition;
use App\Core\Http\Response\Impl\HttpResponse;
use App\Core\Http\Response\Impl\JsonResponse;
use App\Core\Http\Response\Impl\RenderableResponse;
use App\Core\Http\Response\Response;
use App\Core\Http\Response\ResponseFactory;
use App\Core\Template\Contracts\Renderable;
use App\Core\Template\Contracts\TemplateEngine;
use App\Utils\Files;
use App\Utils\Randoms;

class DefaultResponseFactory implements ResponseFactory
{
    public function __construct(
        private CookieQueue $cookieQueue,
        private TemplateEngine $templateEngine
    ) {
        
    }

    #[\Override]
    public function make(mixed $data = null): Response {
        $response = $this->handleDataSpecialCases($data);
        if (!$response) {
            $response = $this->handleDataNormalCases($data);
        }
        return $response;
    }

    private function handleDataSpecialCases(mixed $data) {
        if ($data instanceof Response) {
            return $data;
        }
        if ($data instanceof Renderable) {
            return new RenderableResponse($this->cookieQueue, $data);
        }
        else {
            return false;
        }
    }

    private function handleDataNormalCases(mixed $data) {
        if (isToStringable($data)) {
            return new HttpResponse($this->cookieQueue, strval($data));
        }
        elseif (is_array($data) || is_object($data)) {
            return new JsonResponse($this->cookieQueue, $data);
        }
        else {
            throw new \InvalidArgumentException('Unexpected unsendable data');
        }
    }

    #[\Override]
    public function json(mixed $data): Response {
        return new JsonResponse($this->cookieQueue, $data);
    }

    #[\Override]
    public function view(string $viewName, ?array $context = null): Response {
        $view = $this->templateEngine->view($viewName, $context);
        if (!$view instanceof Renderable) {
            throw new \InvalidArgumentException("View [$viewName] is not renderable");
        }
        return new RenderableResponse($this->cookieQueue, $view);
    }

    #[\Override]
    public function err(int $statusCode, ?string $message = null): Response {
        return $this->make($message)->statusCode($statusCode);
    }

    #[\Override]
    public function errJson(int $statusCode, mixed $data): Response {
        return $this->json($data)->statusCode($statusCode);
    }

    #[\Override]
    public function errView(int $statusCode, string $viewName, ?array $context = null): Response {
        return $this->view($viewName, $context)->statusCode($statusCode);
    }

    #[\Override]
    public function file(string $filename): Response {
        $contentDisposition = new InlineContentDisposition();
        return new FileResponse($this->cookieQueue, $contentDisposition, $filename);
    }

    #[\Override]
    public function download(string $filename, ?string $downloadedFilename = null): Response {
        $downloadedFilename = $downloadedFilename ?: basename($filename);
        $contentDisposition = new DownloadContentDisposition($downloadedFilename);
        return new FileResponse($this->cookieQueue, $contentDisposition, $filename);
    }

    #[\Override]
    public function fileContent(string $fileContent): Response {
        $contentDisposition = new InlineContentDisposition();
        return new ContentResponse($this->cookieQueue, $contentDisposition, $fileContent);
    }

    #[\Override]
    public function downloadContent(string $fileContent, ?string $downloadedFilename = null): Response {
        if (!$downloadedFilename) {
            $downloadedFilename = Randoms::hexString();
            $extension = Files::getFileContentExtension($fileContent);
            if ($extension) {
                $downloadedFilename .= $extension;
            }
        }
        $contentDisposition = new DownloadContentDisposition($downloadedFilename);
        return new ContentResponse($this->cookieQueue, $contentDisposition, $fileContent);
    }
}
