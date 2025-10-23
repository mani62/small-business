<?php

namespace App\Services\Auth\Enums;

enum AuthAction: string
{
    case REGISTER = 'register';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case LOGOUT_ALL = 'logout_all';
    case REFRESH_TOKEN = 'refresh_token';
    case GET_USER_INFO = 'get_user_info';

    public function getDescription(): string
    {
        return match($this) {
            self::REGISTER => 'User Registration',
            self::LOGIN => 'User Login',
            self::LOGOUT => 'User Logout',
            self::LOGOUT_ALL => 'Logout from All Devices',
            self::REFRESH_TOKEN => 'Token Refresh',
            self::GET_USER_INFO => 'Get User Information',
        };
    }

    public function getLogLevel(): string
    {
        return match($this) {
            self::REGISTER, self::LOGIN, self::LOGOUT, self::LOGOUT_ALL, self::REFRESH_TOKEN => 'info',
            self::GET_USER_INFO => 'debug',
        };
    }
}
