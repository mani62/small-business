<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated(), $request);

            return response()->json([
                'message' => 'User registered successfully',
                ...$result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed. Please try again.',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Login user and create token.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated(), $request);

            return response()->json([
                'message' => 'Login successful',
                ...$result
            ], 200);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed. Please try again.',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Logout user and revoke token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user(), $request);

            return response()->json([
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed. Please try again.',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Logout user from all devices (revoke all tokens).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $this->authService->logoutAll($request->user(), $request);

            return response()->json([
                'message' => 'Logged out from all devices successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout from all devices failed. Please try again.',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get authenticated user information.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->getUserInfo($request->user(), $request);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get user information',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Refresh user token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->refreshToken($request->user(), $request);

            return response()->json([
                'message' => 'Token refreshed successfully',
                ...$result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token refresh failed. Please try again.',
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
