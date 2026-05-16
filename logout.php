<?php
/**
 * logout.php — Secure session destruction & redirect.
 */
require_once __DIR__ . '/config.php';

// Clear all session data
$_SESSION = [];

// Expire the session cookie
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
              $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();

header('Location: index.php');
exit();