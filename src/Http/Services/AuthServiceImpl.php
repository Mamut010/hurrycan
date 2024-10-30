<?php
namespace App\Http\Services;

use App\Dal\Contracts\UserRepo;
use App\Http\Contracts\AuthService;
use App\Http\Dtos\AccessTokenPayloadDto;
use App\Http\Dtos\RefreshTokenPayloadDto;
use App\Settings\Auth;
use App\Support\Csrf\CsrfHandler;
use App\Support\Jwt\Exceptions\JwtException;
use App\Support\Jwt\JwtClaim;
use App\Support\Jwt\JwtHandler;
use App\Support\Jwt\JwtOptions;
use App\Utils\Converters;
use App\Utils\Randoms;

class AuthServiceImpl implements AuthService
{
    private const SEQ_KEY = 'seq';

    public function __construct(
        private readonly string $accessTokenSecret,
        private readonly string $refreshTokenSecret,
        private readonly JwtHandler $jwt,
        private readonly CsrfHandler $csrf,
        private readonly UserRepo $userRepo,
    ) {
        
    }

    #[\Override]
    public function issueAccessToken(AccessTokenPayloadDto $dto, string &$csrfToken = null): string {
        $payload = ['id' => $dto->id, 'name' => $dto->name, 'role' => $dto->role];
        $jti = Randoms::uuidv4();
        $csrfToken = $this->csrf->generate($jti);
        return $this->jwt->sign($payload, $this->accessTokenSecret, new JwtOptions([
            JwtClaim::EXPIRATION_TIME => Auth::ACCESS_TOKEN_TTL + time(),
            JwtClaim::JWT_ID => $jti
        ]));
    }

    #[\Override]
    public function issueRefreshToken(RefreshTokenPayloadDto $dto): string {
        $payload = ['id' => $dto->id, static::SEQ_KEY => 1];
        return $this->jwt->sign($payload, $this->refreshTokenSecret, new JwtOptions([
            JwtClaim::EXPIRATION_TIME => Auth::REFRESH_TOKEN_TTL + time()
        ]));
    }

    #[\Override]
    public function verifyAccessToken(string $token, array &$claims = null): AccessTokenPayloadDto|false {
        try {
            $payload = $this->jwt->verify($token, $this->accessTokenSecret, $claims);
            return Converters::arrayToObject($payload, AccessTokenPayloadDto::class);
        }
        catch (JwtException $e) {
            return false;
        }
    }

    #[\Override]
    public function verifyRefreshToken(string $token, array &$claims = null): RefreshTokenPayloadDto|false {
        try {
            $payload = $this->jwt->verify($token, $this->refreshTokenSecret, $claims);
            return Converters::arrayToObject($payload, RefreshTokenPayloadDto::class);
        }
        catch (JwtException $e) {
            return false;
        }
    }

    #[\Override]
    public function verifyCsrfToken(string $token, string $jti): bool {
        return $this->csrf->validate($token, $jti);
    }

    #[\Override]
    public function increaseRefreshSeq(string $token): string|false {
        try {
            $payload = $this->jwt->decode($token, $claims);
            $currentSeq = $payload[static::SEQ_KEY];
            $newPayload = [...$payload, static::SEQ_KEY => $currentSeq + 1];
            return $this->jwt->sign($newPayload, $this->refreshTokenSecret, new JwtOptions($claims));
        }
        catch (JwtException $e) {
            return false;
        }
    }

    #[\Override]
    public function decodeToken(string $token, array &$claims = null): array|false {
        return $this->jwt->decode($token, $claims);
    }
}
