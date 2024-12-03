<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Constants\SameSite;
use App\Core\Http\Cookie\CookieOptions;
use App\Core\Http\Request\Request;
use App\Core\Validation\Attributes\ReqBody;
use App\Dal\Dtos\UserDto;
use App\Http\Contracts\AuthService;
use App\Http\Contracts\UserService;
use App\Http\Dtos\AccessTokenPayloadDto;
use App\Http\Dtos\RefreshTokenClaims;
use App\Http\Requests\CustomerSignUpRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Utils\Responses;
use App\Settings\AuthSetting;
use App\Utils\Converters;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserService $userService,
    ) {
        
    }

    public function showCustomerSignUp() {
        return view('sign-up');
    }

    public function showLogin() {
        return view('login');
    }

    public function customerSignUp(#[ReqBody] CustomerSignUpRequest $signUpRequest) {
        $user = $this->userService->findOneByUsername($signUpRequest->username);
        if ($user) {
            return response()->errJson(HttpCode::CONFLICT, 'Username already exists');
        }

        $success = $this->userService->createCustomer($signUpRequest);
        $msg = $success ? 'Success' : 'Failed to sign up';
        $status = $success ? HttpCode::CREATED : HttpCode::CONFLICT;
        return response()->json($msg)->statusCode($status);
    }

    public function login(Request $request, #[ReqBody] LoginRequest $loginRequest) {
        $user = $this->userService->findOneByUsername($loginRequest->username);
        if (!$user || !password_verify($loginRequest->password, $user->password)) {
            return response()->errJson(HttpCode::CONFLICT, 'Wrong username or password');
        }

        $this->removeExistingAuthRecord($request);
        
        $refreshTokenIssue = $this->authService->issueRefreshToken($user->id);
        $refreshToken = $refreshTokenIssue->token;
        $refreshTokenClaims = $refreshTokenIssue->claims;
        return $this->createFullAuthResponse($user, $refreshToken, $refreshTokenClaims);
    }

    public function logout(Request $request) {
        $this->removeExistingAuthRecord($request);
        
        return response()
                    ->make()
                    ->statusCode(HttpCode::NO_CONTENT)
                    ->withoutCookie(AuthSetting::ACCESS_TOKEN_KEY, static::createAccessCookieOptions())
                    ->withoutCookie(AuthSetting::REFRESH_TOKEN_KEY, static::createRefreshCookieOptions());
    }

    public function reissueTokens(Request $request) {
        $refreshToken = $request->cookie(AuthSetting::REFRESH_TOKEN_KEY);
        if ($refreshToken === false) {
            return response()->errJson(HttpCode::UNAUTHORIZED, "Embedded credential not found");
        }

        $refreshTokenVerifying = $this->authService->verifyRefreshToken($refreshToken);
        if (!$refreshTokenVerifying) {
            return response()->errJson(HttpCode::UNAUTHORIZED, "Invalid embedded credential");
        }

        $user = $refreshTokenVerifying->user;
        $newRefreshToken = $refreshTokenVerifying->newToken;
        $refreshTokenClaims = $refreshTokenVerifying->claims;
        return $this->createFullAuthResponse($user, $newRefreshToken, $refreshTokenClaims);
    }

    private function createFullAuthResponse(UserDto $user, string $refreshToken, RefreshTokenClaims $refreshTokenClaims) {
        $payload = Converters::instanceToObject($user, AccessTokenPayloadDto::class);
        $accessTokenIssue = $this->authService->issueAccessToken($user->id, $payload);

        $accessToken = $accessTokenIssue->token;
        $csrfToken = $accessTokenIssue->csrf;
        $accessTokenExp = $accessTokenIssue->claims->exp;
        $refreshTokenExp = $refreshTokenClaims->exp;

        $responseData = Responses::authResponse($user, $csrfToken, $accessTokenIssue->claims);

        return response()
                    ->json($responseData)
                    ->cookie(
                        AuthSetting::ACCESS_TOKEN_KEY,
                        $accessToken,
                        $accessTokenExp,
                        static::createAccessCookieOptions()
                    )
                    ->cookie(
                        AuthSetting::REFRESH_TOKEN_KEY,
                        $refreshToken,
                        $refreshTokenExp,
                        static::createRefreshCookieOptions()
                    );
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

    private function removeExistingAuthRecord(Request $request) {
        $refreshToken = $request->cookie(AuthSetting::REFRESH_TOKEN_KEY);
        if ($refreshToken) {
            $this->authService->deleteRefreshToken($refreshToken);
        }
    }
}
