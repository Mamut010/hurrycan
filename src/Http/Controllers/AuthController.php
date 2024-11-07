<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Constants\SameSite;
use App\Core\Http\Cookie\CookieOptions;
use App\Core\Http\Request\Request;
use App\Dal\Contracts\UserRepo;
use App\Dal\Models\User;
use App\Http\Contracts\AuthService;
use App\Http\Dtos\AccessTokenPayloadDto;
use App\Http\Dtos\AuthUserDto;
use App\Http\Dtos\RefreshTokenPayloadDto;
use App\Http\Exceptions\BadRequestException;
use App\Http\Exceptions\InternalServerErrorException;
use App\Http\Exceptions\UnauthorizedException;
use App\Settings\Auth;
use App\Utils\Converters;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserRepo $userRepo,
    ) {
        
    }

    public function login(Request $request) {
        if (!$request->has(['username', 'password'])) {
            throw new BadRequestException('Username or password not given');
        }

        $username = $request->string('username');
        $password = $request->string('password');
        $user = $this->userRepo->findOneByUsername($username);
        if (!$user || !password_verify($password, $user->password)) {
            throw new UnauthorizedException("Wrong username or password");
        }
        
        $accessPayload = static::createAccessPayloadFromUser($user);
        $refreshPayload = static::createRefreshPayloadFromUser($user);

        $accessToken = $this->authService->issueAccessToken($accessPayload, $csrfToken);
        $refreshToken = $this->authService->issueRefreshToken($refreshPayload);

        return response()
                    ->json(['csrf' => $csrfToken])
                    ->cookie(
                        Auth::ACCESS_TOKEN_KEY,
                        $accessToken,
                        Auth::ACCESS_TOKEN_TTL,
                        static::createAccessCookieOptions()
                    )
                    ->cookie(
                        Auth::REFRESH_TOKEN_KEY,
                        $refreshToken,
                        Auth::REFRESH_TOKEN_TTL,
                        static::createRefreshCookieOptions()
                    );
    }

    public function logout() {
        return response()
                    ->make()
                    ->statusCode(HttpCode::NO_CONTENT)
                    ->withoutCookie(Auth::ACCESS_TOKEN_KEY, static::createAccessCookieOptions())
                    ->withoutCookie(Auth::REFRESH_TOKEN_KEY, static::createRefreshCookieOptions());
    }

    private static function createAccessPayloadFromUser(User $user) {
        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role?->name,
        ];
        return Converters::arrayToObject($payload, AccessTokenPayloadDto::class);
    }

    private static function createRefreshPayloadFromUser(User $user) {
        $payload = ['id' => $user->id];
        return Converters::arrayToObject($payload, RefreshTokenPayloadDto::class);
    }

    private static function createAccessCookieOptions() {
        $options = new CookieOptions();
        $options->path = '/';
        $options->httponly = true;
        $options->secure = true;
        $options->samesite = SameSite::NONE;
        return $options;
    }

    private static function createRefreshCookieOptions() {
        $options = new CookieOptions();
        $options->httponly = true;
        $options->secure = true;
        $options->samesite = SameSite::NONE;
        return $options;
    }

    public function reissueTokens(Request $request) {
        $refreshToken = $request->cookie(Auth::REFRESH_TOKEN_KEY);
        if ($refreshToken === false) {
            throw new UnauthorizedException("Refresh token not found");
        }

        $payload = $this->authService->verifyRefreshToken($refreshToken);
        if (!$payload) {
            throw new UnauthorizedException("Invalid refresh token");
        }

        $user = $this->userRepo->findOneById($payload->id);
        if (!$user) {
            return response()->err(HttpCode::NOT_FOUND, "User information not found");
        }

        $payload = static::createAccessPayloadFromUser($user);
        $accessToken = $this->authService->issueAccessToken($payload, $csrfToken);
        $newRefreshToken = $this->authService->increaseRefreshSeq($refreshToken);
        if (!$newRefreshToken) {
            throw new InternalServerErrorException('Unable to increase sequence number of refresh token');
        }

        return response()
                    ->json(['csrf' => $csrfToken])
                    ->cookie(
                        Auth::ACCESS_TOKEN_KEY,
                        $accessToken,
                        Auth::ACCESS_TOKEN_TTL,
                        static::createAccessCookieOptions()
                    )
                    ->cookie(
                        Auth::REFRESH_TOKEN_KEY,
                        $newRefreshToken,
                        Auth::REFRESH_TOKEN_TTL,
                        static::createRefreshCookieOptions()
                    );
    }
}
