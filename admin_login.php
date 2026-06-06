<?php

require_once 'db.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $pdo  = get_pdo();
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header('Location: admin_dashboard.php');
            exit;
        }
        $error = 'Invalid credentials.';
    }
}
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — MediCare</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
  <div class="brand">
    <div class="brand-icon">🛡️</div>
    <div class="brand-name">Medi<span>Care</span></div>
    <div class="role-badge">Admin Panel</div>
  </div>
  <?php if ($error): ?>
    <div class="alert">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div class="field">
      <label>Email Address</label>
      <div class="field-wrap">
        <span class="field-icon">✉️</span>
        <input type="email" name="email" placeholder="admin@medicare.com" required>
      </div>
    </div>
    <div class="field">
      <label>Password</label>
      <div class="field-wrap">
        <span class="field-icon">🔒</span>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
    </div>
    <button class="btn" type="submit">Sign In as Admin</button>
  </form>
  <div class="footer-links">
    <a href="index.php">Patient Login</a> | <a href="doctor_login.php">Doctor Login</a>
  </div>
</div>
</body>
</html>
