<?php
/**
 * db.php — Database connection + auth helpers.
 *
 * Loads config.php for credentials so they stay in one place.
 * session.php is bootstrapped here so every file only needs:
 *   require_once 'db.php';
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/config.php';

// ── PDO singleton ────────────────────────────────────────────────────────────
function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            // Do not expose PDO error details in production
            die(json_encode(['error' => 'Database connection failed. Check config.php credentials.']));
        }
    }
    return $pdo;
}

// ── Auth guards ──────────────────────────────────────────────────────────────

function require_patient(): array {
    if (empty($_SESSION['patient_id'])) {
        header('Location: index.php');
        exit;
    }
    return $_SESSION;
}

function require_doctor(): array {
    if (empty($_SESSION['doctor_id'])) {
        header('Location: doctor_login.php');
        exit;
    }
    return $_SESSION;
}

function require_admin(): array {
    if (empty($_SESSION['admin_id'])) {
        header('Location: admin_login.php');
        exit;
    }
    return $_SESSION;
}

// ── CSRF helpers ─────────────────────────────────────────────────────────────

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }
}
