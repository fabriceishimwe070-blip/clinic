<?php

require_once 'db.php';

if (!empty($_SESSION['doctor_id'])) {
    header('Location: doctor_dashboard.php');
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
        $stmt = $pdo->prepare('SELECT * FROM doctors WHERE email = ?');
        $stmt->execute([$email]);
        $doc  = $stmt->fetch();
        if ($doc && password_verify($password, $doc['password'])) {
            $_SESSION['doctor_id']   = $doc['id'];
            $_SESSION['doctor_name'] = $doc['full_name'];
            header('Location: doctor_dashboard.php');
            exit;
        }
        $error = 'Invalid email or password.';
    }
}
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Login — MediCare</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="style.css">
</head>
<body>
 <div style="width:600px; height:500px; overflow:hidden;border-radius:20px;">
    <img src="image.png" style="width:600px; height:550px;">
</div>
<div class="card">
  <div class="brand">
    <div class="brand-icon">👨‍⚕️</div>
    <div class="brand-name">Medi<span>Care</span></div>
    <div class="role-badge">Doctor Portal</div>
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
        <input type="email" name="email" placeholder="doctor@hospital.com" required>
      </div>
    </div>
    <div class="field">
      <label>Password</label>
      <div class="field-wrap">
        <span class="field-icon">🔒</span>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
    </div>
    <button class="btn" type="submit">Sign In as Doctor</button>
  </form>
  <div class="footer-links">
    <a href="index.php">Patient Login</a> | <a href="admin_login.php">Admin Login</a>
  </div>
</div>
</body>
</html>
