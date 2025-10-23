<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Auth\Enums\AuthAction;
use App\Services\Auth\Enums\TokenType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user.
     *
     * @param array $validatedData
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function register(array $validatedData, Request $request): array
    {
        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            $token = $user->createToken(TokenType::AUTH_TOKEN->value)->plainTextToken;

            $this->logAuthAction(AuthAction::REGISTER, $user, $request, true);

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ];

        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'email' => $validatedData['email'] ?? null,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Login user and create token.
     *
     * @param array $credentials
     * @param Request $request
     * @return array
     * @throws ValidationException
     * @throws \Exception
     */
    public function login(array $credentials, Request $request): array
    {
        try {
            if (!Auth::attempt($credentials)) {
                Log::warning('Failed login attempt', [
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = Auth::user();

            $user->tokens()->delete();

            $token = $user->createToken(TokenType::AUTH_TOKEN->value)->plainTextToken;

            $this->logAuthAction(AuthAction::LOGIN, $user, $request, true);

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ];

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'email' => $credentials['email'] ?? null,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Logout user and revoke token.
     *
     * @param User $user
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function logout(User $user, Request $request): void
    {
        try {
            if ($user) {
                $user->currentAccessToken()->delete();

                $this->logAuthAction(AuthAction::LOGOUT, $user, $request, true);
            }
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Logout user from all devices (revoke all tokens).
     *
     * @param User $user
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function logoutAll(User $user, Request $request): void
    {
        try {
            if ($user) {
                $user->tokens()->delete();

                $this->logAuthAction(AuthAction::LOGOUT_ALL, $user, $request, true);
            }
        } catch (\Exception $e) {
            Log::error('Logout from all devices failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Get authenticated user information.
     *
     * @param User $user
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getUserInfo(User $user, Request $request): array
    {
        try {
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            $this->logAuthAction(AuthAction::GET_USER_INFO, $user, $request, true);

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Get user info failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Refresh user token.
     *
     * @param User $user
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function refreshToken(User $user, Request $request): array
    {
        try {
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            $user->currentAccessToken()->delete();

            $token = $user->createToken(TokenType::AUTH_TOKEN->value)->plainTextToken;

            $this->logAuthAction(AuthAction::REFRESH_TOKEN, $user, $request, true);

            return [
                'token' => $token,
                'token_type' => 'Bearer',
            ];

        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'ip' => $request->ip()
            ]);

            throw $e;
        }
    }

    /**
     * Log authentication actions.
     *
     * @param AuthAction $action
     * @param User $user
     * @param Request $request
     * @param bool $success
     * @return void
     */
    private function logAuthAction(AuthAction $action, User $user, Request $request, bool $success = true): void
    {
        $logLevel = $action->getLogLevel();
        $message = $success 
            ? "User {$action->value} successful" 
            : "User {$action->value} failed";

        $context = [
            'action' => $action->value,
            'action_description' => $action->getDescription(),
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => $success
        ];

        Log::{$logLevel}($message, $context);
    }
}
