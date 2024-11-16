<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Constants\SameSite;
use App\Core\Http\Cookie\CookieOptions;
use App\Core\Http\Request\Request;
use App\Core\Validation\Attributes\ReqBody;
use App\Dal\Contracts\UserRepo;
use App\Http\Contracts\AuthService;
use App\Http\Dtos\AccessTokenPayloadDto;
use App\Http\Exceptions\UnauthorizedException;
use App\Http\Requests\LoginRequest;
use App\Settings\Auth;
use App\Utils\Converters;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserRepo $userRepo,
    ) {
        
    }

    public function login(#[ReqBody] LoginRequest $loginRequest) {
        $user = $this->userRepo->findOneByUsername($loginRequest->username);
        if (!$user || !password_verify($loginRequest->password, $user->password)) {
            throw new UnauthorizedException("Wrong username or password");
        }
        
        $accessPayload = Converters::instanceToObject($user, AccessTokenPayloadDto::class);

        $accessTokenIssue = $this->authService->issueAccessToken($user->id, $accessPayload);
        $refreshTokenIssue = $this->authService->issueRefreshToken($user->id);

        $accessToken = $accessTokenIssue->token;
        $csrfToken = $accessTokenIssue->csrf;
        $refreshToken = $refreshTokenIssue->token;
        $accessTokenExp = $accessTokenIssue->claims->exp;
        $refreshTokenExp = $refreshTokenIssue->claims->exp;

        return response()
                    ->json(['csrf' => $csrfToken])
                    ->cookie(
                        Auth::ACCESS_TOKEN_KEY,
                        $accessToken,
                        $accessTokenExp,
                        static::createAccessCookieOptions()
                    )
                    ->cookie(
                        Auth::REFRESH_TOKEN_KEY,
                        $refreshToken,
                        $refreshTokenExp,
                        static::createRefreshCookieOptions()
                    );
    }

    public function logout(Request $request) {
        $refreshToken = $request->cookie(Auth::REFRESH_TOKEN_KEY);
        if ($refreshToken === false) {
            throw new UnauthorizedException("Embedded credential not found");
        }
        $this->authService->deleteRefreshToken($refreshToken);
        return response()
                    ->make()
                    ->statusCode(HttpCode::NO_CONTENT)
                    ->withoutCookie(Auth::ACCESS_TOKEN_KEY, static::createAccessCookieOptions())
                    ->withoutCookie(Auth::REFRESH_TOKEN_KEY, static::createRefreshCookieOptions());
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
            throw new UnauthorizedException("Embedded credential not found");
        }

        $refreshTokenVerifying = $this->authService->verifyRefreshToken($refreshToken);
        if (!$refreshTokenVerifying) {
            throw new UnauthorizedException("Invalid embedded credential");
        }

        $user = $refreshTokenVerifying->user;
        $payload = Converters::instanceToObject($user, AccessTokenPayloadDto::class);
        $accessTokenIssue = $this->authService->issueAccessToken($user->id, $payload);

        $accessToken = $accessTokenIssue->token;
        $csrfToken = $accessTokenIssue->csrf;
        $refreshToken = $refreshTokenVerifying->newToken;
        $accessTokenExp = $accessTokenIssue->claims->exp;
        $refreshTokenExp = $refreshTokenVerifying->claims->exp;

        return response()
                    ->json(['csrf' => $csrfToken])
                    ->cookie(
                        Auth::ACCESS_TOKEN_KEY,
                        $accessToken,
                        $accessTokenExp,
                        static::createAccessCookieOptions()
                    )
                    ->cookie(
                        Auth::REFRESH_TOKEN_KEY,
                        $refreshToken,
                        $refreshTokenExp,
                        static::createRefreshCookieOptions()
                    );
    }
}
