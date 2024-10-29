<?php
namespace App\Core\Template;

use App\Core\Template\Contracts\TemplateEngine;
use App\Core\Template\Contracts\TemplateParser;
use App\Core\Template\Contracts\View;
use App\Utils\Files;
use App\Utils\Paths;
use App\Utils\Strings;

class HurrycanTemplateEngine implements TemplateEngine
{
    public const SUBDIRECTORY_SEPARATOR = '.';
    private const CACHE_DIRECTORY_SUFFIX = '-cache';
    private const PHP_FILE_EXTENSION = '.php';

    private TemplateParser $parser;
    private string $viewsPath;
    private string $viewExtension;
    private array $sharedData;
    private bool $ignoreCache;

    public function __construct(TemplateParser $parser, string $viewsPath, string $viewExtension)
    {
        $this->parser = $parser;
        $this->viewsPath = Paths::normalize($viewsPath);
        $this->viewExtension = $viewExtension;
        $this->sharedData = [];
        $this->ignoreCache = false;
    }

    public function setIgnoreCache(bool $ignoreCache): void {
        $this->ignoreCache = $ignoreCache;
    }

    #[\Override]
    public function share(string $key, string $value): self {
        $this->sharedData[$key] = $value;
        return $this;
    }

    #[\Override]
    public function view(string $viewName, ?array $context = null): View {
        $actualViewName = $this->interpretViewName($viewName);
        $viewFilename = basename($actualViewName);
        $subpath = Strings::rtrimSubstr($actualViewName, $viewFilename);
        $cachePath = $this->getCachePath($subpath);
        
        $outputFilename = $this->getOutputFilename($viewFilename);
        $outputFile = $cachePath . DIRECTORY_SEPARATOR . $outputFilename;

        if ($this->ignoreCache || !file_exists($outputFile)) {
            $content = $this->parseView($actualViewName);
            Files::saveAsFile($content, $cachePath, $outputFilename);
        }

        $params = array_merge($context ?? [], $this->sharedData);
        return new HurrycanView($viewName, $outputFile, $params);
    }

    private function interpretViewName(string $viewName) {
        return str_replace(static::SUBDIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $viewName);
    }

    private function getCachePath(string $subpath) {
        $path = rtrim($this->viewsPath, DIRECTORY_SEPARATOR);
        $pathSegments = explode(DIRECTORY_SEPARATOR, $path);
        $lastIdx = count($pathSegments) - 1;

        $viewsDirectory = $pathSegments[$lastIdx];
        $cacheDirectory = $viewsDirectory . static::CACHE_DIRECTORY_SUFFIX;

        $pathSegments[$lastIdx] = $cacheDirectory;
        $cachePath = implode(DIRECTORY_SEPARATOR, $pathSegments);
        if ($subpath) {
            $subpath = trim($subpath, DIRECTORY_SEPARATOR);
            $cachePath = $cachePath . DIRECTORY_SEPARATOR . $subpath;
        }
        return $cachePath;
    }

    private function getOutputFilename(string $viewName) {
        return $viewName . static::PHP_FILE_EXTENSION;
    }

    /**
     * @param string $view
     * @param array $context
     * @throws \UnexpectedValueException
     */
    private function parseView(string $actualViewName)
    {
        $file = $this->viewsPath . $actualViewName . $this->viewExtension;
        if (!file_exists($file)) {
            throw new \UnexpectedValueException(
                "The view $actualViewName could not be found on $this->viewsPath"
            );
        }

        $content = file_get_contents($file);
        return $this->parser->parse($content);
    }
}
