<?php
namespace App\Support\Csrf;

use App\Core\Http\Session\SessionManager;
use App\Settings\Auth;
use App\Utils\Randoms;

class SynchronizerTokenCsrfHandler implements CsrfHandler
{
    public function __construct(private readonly SessionManager $session) {
        
    }

    #[\Override]
    public function generate(string $data): string {
        $token = Randoms::hexString();
        $this->session->put(Auth::CSRF_TOKEN_KEY, $token);
        return $token;
    }

    #[\Override]
    public function validate(string $csrfToken, string $data): bool {
        $storedToken = $this->session->get(Auth::CSRF_TOKEN_KEY);
        return $csrfToken === $storedToken;
    }
}
