<?php
declare(strict_types=1);

namespace Core;

/** Simple session-based CSRF protection for POST forms. */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }

    public static function verify(): bool
    {
        $sent = $_POST['_token'] ?? '';
        return is_string($sent) && hash_equals(self::token(), $sent);
    }
}
