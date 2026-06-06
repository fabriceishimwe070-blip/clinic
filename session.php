<?php
/**
 * session.php — Single authoritative session bootstrap.
 * Include this ONCE at the top of every file that needs sessions.
 * All other files must NOT call session_start() directly.
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Set to 1 on HTTPS hosts; leave 0 for HTTP-only shared hosting
    ini_set('session.cookie_secure', 0);
    ini_set('session.use_strict_mode', 1);
    session_start();
}
