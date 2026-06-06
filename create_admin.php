<?php


require_once 'db.php';

$username = 'admin';
$email    = 'fabriceishimwe070@gmail.com';
$password = 'fabrice2007';   // ← Change this before running!

$hash = password_hash($password, PASSWORD_BCRYPT);
$pdo  = get_pdo();

try {
    $pdo->prepare('INSERT INTO admins (username, email, password) VALUES (?,?,?)')
        ->execute([$username, $email, $hash]);
    echo "<h2 style='font-family:sans-serif;color:green'>✅ Admin created!</h2>
          <p>Email: <strong>$email</strong></p>
          <p>Password: <strong>$password</strong></p>
          <p style='color:red'><strong>⚠ Delete this file now!</strong></p>";
} catch (PDOException $e) {
    echo "<h2 style='font-family:sans-serif;color:red'>Admin already exists or error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
