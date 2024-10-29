<?php
namespace App\Core\Global;

use App\Constants\HttpMethod;

class SuperglobalGlobalCollection implements GlobalCollection
{
    // Cache Body
    private ?array $body;
    // Cache Headers
    private array $headers;

    public function __construct() {
        $this->body = null;
        $headers = getallheaders();
        $this->headers = $headers !== false ? $headers : [];
    }

    #[\Override]
    public function server(): array {
        return $_SERVER;
    }

    #[\Override]
    public function get(): array {
        return $_GET;
    }

    #[\Override]
    public function post(): array {
        return $_POST;
    }

    #[\Override]
    public function body(): array {
        if ($this->body !== null) {
            return $this->body;
        }

        if (!empty($_POST)) {
            $this->body = $_POST;
        }
        elseif ($_SERVER['REQUEST_METHOD'] !== HttpMethod::GET) {
            $this->body = json_decode(file_get_contents('php://input'), true);
        }

        $this->body ??= [];
        return $this->body;
    }

    #[\Override]
    public function files(): array {
        return $_FILES;
    }

    #[\Override]
    public function cookie(): array {
        return $_COOKIE;
    }

    #[\Override]
    public function headers(): array {
        return $this->headers;
    }
}
