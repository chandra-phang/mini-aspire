<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\ApiFormatter;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|min:10',
        ]);
        // Return errors if validation error occur.
        if ($validator->fails()) {
            return ApiFormatter::response(false, $validator->errors(), 400);
        }
        // Check if validation pass then create user and auth token. Return the auth token
        if ($validator->passes()) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'is_admin' => $request->isAdmin,
                'password' => Hash::make($request->password)
            ]);
        
            return ApiFormatter::response(true, 'User created successfully');
        }
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return ApiFormatter::response(false, 'Invalid login details', 401);
        }
        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
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
