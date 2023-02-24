<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterAuthRequest;
use App\Http\Requests\LoginAuthRequest;
use App\Helpers\ApiFormatter;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request, RegisterAuthRequest $validator)
    {
        // Validate request body
        list($valid, $errorsMsg) = $validator->validate($request);
        if (!$valid) {
            return ApiFormatter::response(false, $errorsMsg, 400);
        }
        
        $this->authService->createUser($request);
        return ApiFormatter::response(true, 'User created successfully');
    }

    public function login(Request $request, LoginAuthRequest $validator)
    {
        // Validate request body
        list($valid, $errorsMsg) = $validator->validate($request);
        if (!$valid) {
            return ApiFormatter::response(false, $errorsMsg, 401);
        }

        $token = $this->authService->createToken($request);
        return ApiFormatter::accessTokenResponse(true, $token);
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    public function home(Request $request)
    {
        return ApiFormatter::response(false, 'You are not authorized to access this page', 401);
    }
}
