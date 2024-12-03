<?php
namespace App\Support\Csrf;

use App\Core\Http\Session\SessionManager;
use App\Settings\AuthSetting;
use App\Utils\Randoms;

class SynchronizerTokenCsrfHandler implements CsrfHandler
{
    public function __construct(private readonly SessionManager $session) {
        
    }

    #[\Override]
    public function generate(string $data): string {
        $token = Randoms::hexString();
        $this->session->put(AuthSetting::CSRF_TOKEN_KEY, $token);
        return $token;
    }

    #[\Override]
    public function validate(string $csrfToken, string $data): bool {
        $storedToken = $this->session->get(AuthSetting::CSRF_TOKEN_KEY);
        return $csrfToken === $storedToken;
    }
}
