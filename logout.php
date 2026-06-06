<?php
/**
 * logout.php — Destroys session and redirects by role.
 * Usage: logout.php?role=patient|doctor|admin
 */

require_once 'session.php';

$role = $_GET['role'] ?? 'patient';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

switch ($role) {
    case 'doctor': header('Location: doctor_login.php'); break;
    case 'admin':  header('Location: admin_login.php');  break;
    default:       header('Location: land.php');         break;
}
exit;
