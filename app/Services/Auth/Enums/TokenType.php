<?php

namespace App\Services\Auth\Enums;

enum TokenType: string
{
    case AUTH_TOKEN = 'auth-token';
    case REFRESH_TOKEN = 'refresh-token';
    case API_TOKEN = 'api-token';

    public function getDescription(): string
    {
        return match($this) {
            self::AUTH_TOKEN => 'Authentication Token',
            self::REFRESH_TOKEN => 'Refresh Token',
            self::API_TOKEN => 'API Token',
        };
    }

    public function getExpirationHours(): int
    {
        return match($this) {
            self::AUTH_TOKEN => 24,
            self::REFRESH_TOKEN => 168, // 7 days
            self::API_TOKEN => 8760, // 1 year
        };
    }
}
