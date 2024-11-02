<?php
namespace App\Core\Http\Request;

use App\Core\Http\Cookie\CookieReader;
use App\Core\Http\File\UploadedFile;
use App\Core\Http\Request\Traits\RequestHeaderQueryable;
use App\Core\Http\Request\Traits\RequestInputQueryable;
use App\Core\Http\Request\Traits\RequestUriQueryable;
use App\Core\Http\Session\SessionManager;
use App\Utils\Arrays;

class HttpRequest implements Request
{
    use RequestHeaderQueryable;
    use RequestInputQueryable;
    use RequestUriQueryable;

    private RequestGlobalCollection $global;
    private SessionManager $sessionManager;
    private CookieReader $cookieReader;
    private array $routeParams = [];
    private array $extras = [];

    public function __construct(
        RequestGlobalCollection $global,
        SessionManager $sessionManager,
        CookieReader $cookieReader,
    ) {
        $this->global = $global;
        $this->sessionManager = $sessionManager;
        $this->cookieReader = $cookieReader;
    }

    public function __get($name)
    {
        return Arrays::getOrDefaultExists($this->inputs(), $name, $this->routeParam($name));
    }

    #[\Override]
    public function method(): string {
        $server = $this->global->server();
        return $server['REQUEST_METHOD'];
    }

    #[\Override]
    public function isMethod(string $method): bool {
        return strcasecmp($this->method(), $method) === 0;
    }

    #[\Override]
    public function ipAddress(): string|false {
        $server = $this->global->server();
        $possibleSources = [
            'REMOTE_ADDR', 'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED'
        ];

        $ipAddress = false;
        foreach ($possibleSources as $source) {
            if (isset($server[$source])) {
                $ipAddress = $server[$source];
            }
        }
        return $ipAddress;
    }

    #[\Override]
    public function schemeAndHost(): string {
        return $this->scheme() . '://' . $this->httpHost();
    }

    #[\Override]
    public function body(): array {
        return $this->global->body();
    }

    #[\Override]
    public function cookie(string $name): string|false {
        $value = Arrays::getOrDefault($this->global->cookie(), $name);
        if ($value === null) {
            return false;
        }
        return $this->cookieReader->read($value);
    }

    #[\Override]
    public function routeParams(): array {
        return $this->routeParams;
    }

    #[\Override]
    public function routeParam(string $name, ?string $default = null): ?string {
        return Arrays::getOrDefault($this->routeParams, $name, $default);
    }

    #[\Override]
    public function merge(array $data): self {
        foreach ($data as $key => $value) {
            $this->extras[$key] = $value;
        }
        return $this;
    }

    #[\Override]
    public function mergeMissing(array $data): self {
        $inputs = $this->inputs();
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $inputs)) {
                $this->extras[$key] = $value;
            }
        }
        return $this;
    }

    #[\Override]
    public function addRouteParams(array $params): self {
        foreach ($params as $key => $value) {
            $this->routeParams[$key] = $value;
        }
        return $this;
    }

    #[\Override]
    public function uri(): string {
        $server = $this->global->server();
        return $server["REQUEST_SCHEME"].'://'.$server["HTTP_HOST"].$server['REQUEST_URI'];
    }

    #[\Override]
    public function inputs(): array {
        return array_merge($this->queryAll(), $this->body(), $this->extras);
    }

    #[\Override]
    public function headers(): array {
        return $this->global->headers();
    }

    #[\Override]
    public function session(): SessionManager {
        return $this->sessionManager;
    }

    #[\Override]
    public function hasFile(string $name): bool {
        $files = $this->global->files();
        return isset($files[$name]);
    }

    #[\Override]
    public function file(string $name): ?UploadedFile {
        $files = $this->global->files();
        if (!isset($files[$name])) {
            return null;
        }

        $file = $files[$name];
        try {
            $tmpPath = $file['tmp_name'];
            $originalName = $file['name'];
            $mimeType = Arrays::getOrDefault($file, 'type');
            $error = Arrays::getOrDefault($file, 'error');
            return new UploadedFile($tmpPath, $originalName, $mimeType, $error);
        }
        catch (\Exception $e) {
            return null;
        }
    }
}
