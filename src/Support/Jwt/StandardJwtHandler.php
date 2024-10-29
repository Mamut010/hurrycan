<?php
namespace App\Support\Jwt;

use App\Support\Jwt\Exceptions\InvalidSignatureException;
use App\Support\Jwt\Exceptions\InvalidTokenException;
use App\Support\Jwt\Exceptions\PayloadDecodingException;
use App\Support\Jwt\Exceptions\TokenExpiredException;
use App\Support\Jwt\Exceptions\TokenNotBeforeException;
use App\Support\Jwt\JwtHandler;
use App\Support\Jwt\JwtOptions;
use App\Utils\Arrays;
use App\Utils\Crypto;

class StandardJwtHandler implements JwtHandler
{
    private const LIST_KEY = 'data';
    private const JWT_SEPARATOR = '.';

    private string $header;

    public function __construct() {
        $this->header = json_encode([
            "alg" => "HS256",
            "typ" => "JWT"
        ]);
    }

    #[\Override]
    public function sign(array $payload, string $key, ?JwtOptions $options = null): string {
        if (array_is_list($payload)) {
            $payload = $this->transformListToAssoc($payload);
        }
        if ($options !== null) {
            $payload = $this->mergeOptionsIntoPayload($payload, $options);
        }

        $header = Crypto::base64UrlEncode($this->header);
        $payload = Crypto::base64UrlEncode(json_encode($payload));
        $headerAndPayload = $header . static::JWT_SEPARATOR . $payload;
        $signature = Crypto::generateSignature($headerAndPayload, $key);

        return $headerAndPayload . static::JWT_SEPARATOR . $signature;
    }

    #[\Override]
    public function verify(string $token, string $key, array &$claims = null): array
    {
        $claims = [];
        $parts = static::extractTokenParts($token);
        if (!$parts) {
            throw new InvalidTokenException($token, "Given [$token] is not a valid JWT token");
        }

        $header = $parts['header'];
        $signature = $parts['signature'];
        $attachedPayload = $parts['payload'];

        if (!Crypto::isValidSignature($signature, $header . static::JWT_SEPARATOR . $attachedPayload, $key)) {
            throw new InvalidSignatureException($token, "Given token [$token] has invalid signature");
        }

        $payload = static::jsonDecodePayload($attachedPayload);
        if ($payload === false) {
            throw new PayloadDecodingException($token, "Unable to decode the payload of token [$token]");
        }

        static::ensureClaimsCompliance($token, $payload);
        return $this->recoverPayload($payload, $claims);
    }

    #[\Override]
    public function decode(string $token, array &$claims = null): array|false
    {
        $claims = [];
        $parts = static::extractTokenParts($token);
        if (!$parts) {
            return false;
        }

        $attachedPayload = $parts['payload'];

        $payload = static::jsonDecodePayload($attachedPayload);
        if ($payload === false) {
            return false;
        }

        return $this->recoverPayload($payload, $claims);
    }

    protected function transformListToAssoc(array $list): array {
        return [static::LIST_KEY => $list];
    }

    protected function mergeOptionsIntoPayload(array $payload, JwtOptions $options): array {
        return array_merge($payload, $options->toArray());
    }

    protected function recoverPayload(array $payload, array &$claims): array {
        if (array_key_exists(static::LIST_KEY, $payload) && Arrays::isList($payload[static::LIST_KEY])) {
            return $this->retrieveListPayload($payload, $claims);
        }
        else {
            return $this->stripOptionsFromPayload($payload, $claims);
        }
    }

    protected function retrieveListPayload(array $payload, array &$claims) {
        $originalPayload = $payload[static::LIST_KEY];
        $options = JwtOptions::getOptions();
        $claims = static::getClaimsInPayload($payload, $options);
        return $originalPayload;
    }

    protected function stripOptionsFromPayload(array $payload, array &$claims) {
        $options = JwtOptions::getOptions();
        $originalPayload = Arrays::filterKeys($payload, $options);
        if ($originalPayload !== null) {
            $claims = static::getClaimsInPayload($payload, $options);
        }
        return $originalPayload;
    }

    private function getClaimsInPayload(array $payload, array $options) {
        $claims = [];
        foreach ($options as $option) {
            if (isset($payload[$option])) {
                $claims[$option] = $payload[$option];
            }
        }
        return $claims;
    }

    private static function extractTokenParts(string $token) {
        $jwtTokenPattern = "/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/";
        if (!preg_match($jwtTokenPattern, $token, $matches)) {
            return false;
        }
        return $matches;
    }

    private static function jsonDecodePayload(string $attachedPayload): array|false {
        $decodedPayload = json_decode(Crypto::base64UrlDecode($attachedPayload), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        return $decodedPayload;
    }

    private static function ensureClaimsCompliance(string $token, array $payload) {
        $now = time();
        if (array_key_exists(JwtClaim::EXPIRATION_TIME, $payload) && $now > $payload[JwtClaim::EXPIRATION_TIME]) {
            throw new TokenExpiredException($token, $payload[JwtClaim::EXPIRATION_TIME]);
        }
        if (array_key_exists(JwtClaim::NOT_BEFORE, $payload) && $now < $payload[JwtClaim::NOT_BEFORE]) {
            throw new TokenNotBeforeException($token, $payload[JwtClaim::NOT_BEFORE]);
        }
    }
}
