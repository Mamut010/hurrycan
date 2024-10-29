<?php

namespace App\Utils;

class Sessions
{
    private function __construct() {
        // STATIC CLASS SHOULD NOT BE INSTANTIATED
    }

    public static function isStarted() {
        if (version_compare(phpversion(), "5.4.0") !== -1) {
            return session_status() !== PHP_SESSION_NONE;
        }
        else {
            return session_id() !== '';
        }
    }
}
