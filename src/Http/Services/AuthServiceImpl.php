<?php
namespace App\Http\Services;

use App\Dal\Contracts\RefreshTokenRepo;
use App\Dal\Input\RefreshTokenCreate;
use App\Dal\Input\RefreshTokenUpdate;
use App\Http\Contracts\AuthService;
use App\Http\Dtos\AccessTokenClaims;
use App\Http\Dtos\AccessTokenDto;
use App\Http\Dtos\AccessTokenIssueDto;
use App\Http\Dtos\AccessTokenPayloadDto;
use App\Http\Dtos\RefreshTokenClaims;
use App\Http\Dtos\RefreshTokenIssueDto;
use App\Http\Dtos\RefreshTokenPayloadDto;
use App\Http\Dtos\RefreshTokenVerifyingDto;
use App\Http\Exceptions\ConflictException;
use App\Http\Exceptions\ForbiddenException;
use App\Settings\Auth;
use App\Support\Csrf\CsrfHandler;
use App\Support\Jwt\Exceptions\JwtException;
use App\Support\Jwt\JwtHandler;
use App\Support\Jwt\JwtOptions;
use App\Support\Logger\Logger;
use App\Utils\Converters;
use App\Utils\Crypto;
use App\Utils\Uuids;

class AuthServiceImpl implements AuthService
{
    private const SEQ_MIN = 0;
    private const SEQ_MAX = 2147483647;

    public function __construct(
        private readonly string $accessTokenSecret,
        private readonly string $refreshTokenSecret,
        private readonly JwtHandler $jwt,
        private readonly CsrfHandler $csrf,
        private readonly RefreshTokenRepo $refreshTokenRepo,
    ) {
        
    }

    #[\Override]
    public function issueAccessToken(int $userId, AccessTokenPayloadDto $payload): AccessTokenIssueDto {
        $payload = Converters::objectToArray($payload);
        $jti = Uuids::uuidv4();
        $now = time();
        $exp = Auth::ACCESS_TOKEN_TTL + $now;

        $claims = new AccessTokenClaims();
        $claims->jti = $jti;
        $claims->sub = $userId;
        $claims->iat = $now;
        $claims->exp = $exp;
        $options = new JwtOptions($claims);
        $token = $this->jwt->sign($payload, $this->accessTokenSecret, $options);

        $dto = new AccessTokenIssueDto();
        $dto->token = $token;
        $dto->csrf = $this->csrf->generate($jti);
        $dto->claims = $claims;
        return $dto;
    }

    #[\Override]
    public function issueRefreshToken(int $userId): RefreshTokenIssueDto {
        $jti = Uuids::uuidv4();
        $seq = random_int(static::SEQ_MIN, static::SEQ_MAX);
        $now = time();
        $exp = Auth::REFRESH_TOKEN_TTL + $now;

        $payload = new RefreshTokenPayloadDto();
        $payload->seq = $seq;
        $claims = new RefreshTokenClaims();
        $claims->jti = $jti;
        $claims->sub = $userId;
        $claims->iat = $now;
        $claims->exp = $exp;

        $payload = Converters::objectToArray($payload);
        $options = new JwtOptions($claims);
        $token = $this->jwt->sign($payload, $this->refreshTokenSecret, $options);

        $data = new RefreshTokenCreate();
        $data->jti = Uuids::uuidToBinary($jti);
        $data->hash = Crypto::hash($token, $this->refreshTokenSecret);
        $data->userId = $userId;
        $data->issuedAt = Converters::timestampToDate($now);
        $data->expiresAt = Converters::timestampToDate($exp);
        if (!$this->refreshTokenRepo->create($data)) {
            throw new ConflictException('Unable to generate a valid credential');
        }

        $dto = new RefreshTokenIssueDto();
        $dto->token = $token;
        $dto->claims = $claims;
        return $dto;
    }

    #[\Override]
    public function verifyAccessToken(string $token): AccessTokenDto|false {
        try {
            $tokenContent = $this->jwt->verify($token, $this->accessTokenSecret);
            $dto = new AccessTokenDto();
            $dto->payload = Converters::arrayToObject($tokenContent->payload, AccessTokenPayloadDto::class);
            $dto->claims = Converters::instanceToObject($tokenContent->claims, AccessTokenClaims::class);
            return $dto;
        }
        catch (JwtException $e) {
            return false;
        }
    }

    #[\Override]
    public function verifyRefreshToken(string $token): RefreshTokenVerifyingDto|false {
        try {
            $tokenContent = $this->jwt->verify($token, $this->refreshTokenSecret);
        }
        catch (JwtException $e) {
            return false;
        }

        $payload = Converters::arrayToObject($tokenContent->payload, RefreshTokenPayloadDto::class);
        $claims = Converters::instanceToObject($tokenContent->claims, RefreshTokenClaims::class);
        
        $jti = Uuids::uuidToBinary($claims->jti);
        $refreshToken = $this->refreshTokenRepo->findOneById($jti);
        if (!$refreshToken) {
            $this->handleAbnormalActivity($claims);
        }

        $suppliedHash = Crypto::hash($token, $this->refreshTokenSecret);
        if (!hash_equals($refreshToken->hash, $suppliedHash)) {
            $this->refreshTokenRepo->delete($jti);
            $this->handleAbnormalActivity($claims);
        }

        $payload->seq = static::getNextSeq($payload->seq);
        $payload = Converters::objectToArray($payload);
        $options = new JwtOptions($claims);
        $newToken = $this->jwt->sign($payload, $this->refreshTokenSecret, $options);

        $data = new RefreshTokenUpdate();
        $data->hash = Crypto::hash($newToken, $this->refreshTokenSecret);
        if (!$this->refreshTokenRepo->update($jti, $data)) {
            throw new ConflictException('Unable to generate a valid credential');
        }

        $dto = new RefreshTokenVerifyingDto();
        $dto->newToken = $newToken;
        $dto->claims = $claims;
        $dto->user = $refreshToken->user;
        return $dto;
    }

    #[\Override]
    public function verifyCsrfToken(string $token, string $jti): bool {
        return $this->csrf->validate($token, $jti);
    }

    #[\Override]
    public function deleteRefreshToken(string $token): void {
        $tokenContent = $this->jwt->decode($token);
        $jti = $tokenContent->claims->jti;
        if ($jti) {
            $jti = Uuids::uuidToBinary($jti);
            $this->refreshTokenRepo->delete($jti);
        }
    }

    #[\Override]
    public function decodeAccessToken(string $token): AccessTokenDto|false {
        $tokenContent = $this->jwt->decode($token);
        if (!$tokenContent) {
            return false;
        }
        $dto = new AccessTokenDto();
        $dto->payload = Converters::arrayToObject($tokenContent->payload, AccessTokenPayloadDto::class);
        $dto->claims = Converters::instanceToObject($tokenContent->claims, AccessTokenClaims::class);
        return $dto;
    }

    private function handleAbnormalActivity(RefreshTokenClaims $claims) {
        Logger::securityWarning("Abnormal activity detected! Potential token misuse of user '$claims->sub'");
        throw new ForbiddenException();
    }

    private static function getNextSeq(int $seq) {
        return $seq === static::SEQ_MAX ? static::SEQ_MIN : $seq + 1;
    }
}
