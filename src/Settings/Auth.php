<?php
namespace App\Settings;

final class Auth
{
    const ACCESS_TOKEN_KEY = 'hurrycan$access';
    const REFRESH_TOKEN_KEY = 'hurrycan$refresh';
    const CSRF_TOKEN_KEY = 'csrf-token';

    /**
     * 30 days
     */
    const REFRESH_TOKEN_TTL = 30 * 24 * 60 * 60;

    /**
     * 15 minutes
     */
    const ACCESS_TOKEN_TTL = 15 * 60;
}
