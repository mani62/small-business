<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('User Registration', function () {
    it('can register with valid data', function () {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
                'token',
                'token_type',
            ])
            ->assertJson([
                'message' => 'User registered successfully',
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
                'token_type' => 'Bearer',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    });

    it('fails with invalid data', function () {
        $userData = [
            'name' => 'J', // Too short
            'email' => 'invalid-email', // Invalid email
            'password' => '123', // Too short and weak
            'password_confirmation' => '456', // Doesn't match
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'password_confirmation']);
    });

    it('fails with duplicate email', function () {
        User::factory()->create(['email' => 'john@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('validates password strength requirements', function () {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak', // Too weak
            'password_confirmation' => 'weak',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    it('validates email format', function () {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email-format',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('validates name length', function () {
        $userData = [
            'name' => 'J', // Too short
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('has rate limiting', function () {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        // Make 6 requests (limit is 5)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/auth/register', $userData);
        }

        $response->assertStatus(429);
    });
});

describe('User Login', function () {
    it('can login with valid credentials', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
                'token',
                'token_type',
            ])
            ->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'email' => 'john@example.com',
                ],
                'token_type' => 'Bearer',
            ]);
    });

    it('fails with invalid credentials', function () {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'WrongPassword',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('fails with non-existent email', function () {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('has rate limiting', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'WrongPassword',
        ];

        // Make 6 requests (limit is 5)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/auth/login', $loginData);
        }

        $response->assertStatus(429);
    });
});

describe('User Authentication', function () {
    it('allows authenticated user to access protected route', function () {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    });

    it('denies unauthenticated user access to protected route', function () {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    });
});

describe('User Logout', function () {
    it('can logout successfully', function () {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);

        // Verify token is revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    });

    it('can logout from all devices', function () {
        $user = User::factory()->create();
        $token1 = $user->createToken('auth-token-1')->plainTextToken;
        $token2 = $user->createToken('auth-token-2')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->postJson('/api/auth/logout-all');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out from all devices successfully',
            ]);

        // Verify all tokens are revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    });
});

describe('Token Management', function () {
    it('can refresh token', function () {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'token_type',
            ])
            ->assertJson([
                'message' => 'Token refreshed successfully',
                'token_type' => 'Bearer',
            ]);

        // Verify old token is revoked and new token is created
        $this->assertDatabaseCount('personal_access_tokens', 1);
    });
});