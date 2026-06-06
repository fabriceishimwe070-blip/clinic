<?php

require_once 'db.php';


if (!empty($_SESSION['patient_id'])) {
    header('Location: home.php');
    exit;
}

$errors   = [];
$success  = '';
$tab      = 'login';


if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $tab = 'register';
    csrf_verify();

    $fname    = trim($_POST['name']     ?? '');
    $lname    = trim($_POST['lname']    ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $country  = trim($_POST['country']  ?? '');
    $password = $_POST['password']      ?? '';

 
    if (!$fname)                            $errors[] = 'First name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if (strlen($phone) < 7)                 $errors[] = 'Enter a valid phone number.';
    if (strlen($password) < 8)              $errors[] = 'Password must be at least 8 characters.';

    if (!$errors) {
        $pdo  = get_pdo();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already registered. Please sign in.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $full = $fname . ($lname ? ' ' . $lname : '');
            $ins  = $pdo->prepare(
                'INSERT INTO users (Fname, email, phon, country, password) VALUES (?,?,?,?,?)'
            );
            $ins->execute([$full, $email, $phone, $country, $hash]);
            $success = 'Account created! You can now sign in.';
            $tab = 'login';
        }
    }
}


if (isset($_POST['action']) && $_POST['action'] === 'login') {
    csrf_verify();

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (!$email || !$password) {
        $errors[] = 'Please fill in all fields.';
    } else {
        $pdo  = get_pdo();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['patient_id']   = $user['id'];
            $_SESSION['patient_name'] = $user['Fname'];
            $_SESSION['patient_email']= $user['email'];
            header('Location: home.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MediCare — Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    :root{--navy:#04122a;--navy-mid:#0c2345;--teal:#2b9a78;--teal-glow:#3ecfa0;--gold:#e8b84b;--cream:rgba(226,234,214,.85);--cream-dim:rgba(226,234,214,.4);--glass:rgba(255,255,255,.05);--glass-border:rgba(255,255,255,.1);--error:#f87171}
    *{margin:0;padding:0;box-sizing:border-box}
    body{background-color:var(--navy);font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;overflow-y:scroll;background-image:radial-gradient(ellipse 70% 60% at 15% 15%,rgba(43,154,120,.13) 0%,transparent 55%),radial-gradient(ellipse 55% 50% at 85% 80%,rgba(12,35,69,.9) 0%,transparent 65%)}
    .bg-grid{position:fixed;inset:0;background-image:linear-gradient(rgba(62,207,160,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(62,207,160,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;z-index:0}
    .bg-orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0}
    .orb1{width:420px;height:420px;background:radial-gradient(circle,rgba(43,154,120,.18),transparent 70%);top:-100px;left:-100px;animation:drift 12s ease-in-out infinite alternate}
    .orb2{width:300px;height:300px;background:radial-gradient(circle,rgba(232,184,75,.1),transparent 70%);bottom:-80px;right:-80px;animation:drift 15s ease-in-out infinite alternate-reverse}
    @keyframes drift{from{transform:translate(0,0) scale(1)}to{transform:translate(30px,20px) scale(1.05)}}
    .page-wrapper{position:relative;z-index:1;display:flex;width:100%;max-width:980px;min-height:620px;border-radius:2rem;overflow:hidden;box-shadow:0 40px 80px rgba(0,0,0,.6);border:1px solid var(--glass-border)}
    .left-panel{flex:1;background:linear-gradient(145deg,#0b2e22,#072238);padding:3rem 2.5rem;display:flex;flex-direction:column;justify-content:space-between;position:relative;overflow:hidden}
    .brand-logo{display:flex;align-items:center;gap:12px;margin-bottom:2.5rem}
    .brand-icon{width:44px;height:44px;background:linear-gradient(135deg,var(--teal),#1a6b54);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;box-shadow:0 0 20px rgba(62,207,160,.3)}
    .brand-name{font-family:'Cormorant Garamond',serif;font-size:1.6rem;font-weight:700;color:#fff;letter-spacing:.5px}
    .brand-name span{color:var(--teal-glow)}
    .left-headline{font-family:'Cormorant Garamond',serif;font-size:2.6rem;font-weight:600;color:#fff;line-height:1.2;margin-bottom:1rem}
    .left-headline em{color:var(--teal-glow);font-style:normal}
    .left-sub{color:var(--cream-dim);font-size:.92rem;line-height:1.7;font-weight:300}
    .left-footer{font-size:.78rem;color:rgba(226,234,214,.2);font-weight:300}
    .right-panel{width:420px;background:rgba(4,18,42,.95);backdrop-filter:blur(20px);padding:3rem 2.5rem;display:flex;flex-direction:column;justify-content:center;border-left:1px solid var(--glass-border)}
    .form-title{font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:600;color:#fff;margin-bottom:.4rem}
    .form-sub{font-size:.85rem;color:var(--cream-dim);font-weight:300;margin-bottom:1.5rem}
    .tabs{display:flex;background:rgba(255,255,255,.05);border:1px solid var(--glass-border);border-radius:50px;padding:4px;margin-bottom:1.8rem}
    .tab-btn{flex:1;padding:.55rem;border:none;border-radius:50px;font-family:'DM Sans',sans-serif;font-size:.88rem;font-weight:500;cursor:pointer;transition:all .25s;background:none;color:var(--cream-dim)}
    .tab-btn.active{background:var(--teal);color:#fff;box-shadow:0 0 12px rgba(43,154,120,.4)}
    .form-section{display:none}.form-section.active{display:block}
    .field{margin-bottom:1.1rem}
    .field label{display:block;font-size:.8rem;font-weight:500;color:var(--cream-dim);margin-bottom:.45rem;letter-spacing:.3px;text-transform:uppercase}
    .field-wrap{position:relative}
    .field-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-size:1rem;pointer-events:none;opacity:.5}
    .field input,.field select{width:100%;padding:.85rem 1rem .85rem 2.7rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:12px;font-family:'DM Sans',sans-serif;font-size:.92rem;color:var(--cream);outline:none;transition:all .25s;appearance:none}
    .field select option{background:#0c2345;color:#e2ead6}
    .field input::placeholder{color:rgba(226,234,214,.25)}
    .field input:focus,.field select:focus{border-color:var(--teal-glow);background:rgba(62,207,160,.06);box-shadow:0 0 0 3px rgba(62,207,160,.12)}
    .phone-row{display:flex;gap:.6rem}
    .phone-row .country-code{width:110px;flex-shrink:0;padding-left:1rem}
    .phone-row .phone-num{flex:1;padding-left:1rem}
    .row-2{display:flex;gap:.7rem}.row-2 .field{flex:1}
    .terms{display:flex;align-items:flex-start;gap:10px;margin:.8rem 0}
    .terms input[type=checkbox]{width:16px;height:16px;accent-color:var(--teal-glow);margin-top:2px;flex-shrink:0}
    .terms label{font-size:.78rem;color:var(--cream-dim);line-height:1.5;cursor:pointer;text-transform:none;letter-spacing:0}
    .terms label a{color:var(--teal-glow);text-decoration:none}
    .submit-btn{width:100%;padding:.95rem;background:linear-gradient(135deg,var(--teal),#1a6b54);border:none;border-radius:12px;color:#fff;font-family:'DM Sans',sans-serif;font-size:1rem;font-weight:500;cursor:pointer;transition:all .25s;margin-top:.5rem;letter-spacing:.3px}
    .submit-btn:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(43,154,120,.4)}
    .alert{padding:.7rem 1rem;border-radius:10px;font-size:.85rem;margin-bottom:1rem}
    .alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--error)}
    .alert-success{background:rgba(62,207,160,.1);border:1px solid rgba(62,207,160,.3);color:var(--teal-glow)}
    .other-logins{margin-top:1.2rem;text-align:center;font-size:.8rem;color:var(--cream-dim)}
    .other-logins a{color:var(--teal-glow);text-decoration:none;margin:0 6px}
    .other-logins a:hover{text-decoration:underline}
    @media(max-width:760px){.page-wrapper{flex-direction:column;border-radius:1.5rem}.left-panel{padding:2rem 1.8rem 1.5rem}.left-headline{font-size:2rem}.right-panel{width:100%;padding:2rem 1.8rem}}
  </style>
</head>
<body>
<div class="bg-grid"></div>
<div class="bg-orb orb1"></div>
<div class="bg-orb orb2"></div>

<div class="page-wrapper">
  <div class="left-panel">
    <div>
      <div class="brand-logo">
        <div class="brand-icon">🩺</div>
        <div class="brand-name">Medi<span>Care</span></div>
      </div>
      <div class="left-headline">Your health,<br>our <em>priority</em>.</div>
      <p class="left-sub">Book appointments, connect with specialists, and manage your health — all in one place.</p>
    </div>
    <div><img src="dr.png" width="500px" ></div>
    <div class="left-footer">© 2026 MediCare Health Platform. All rights reserved.</div>
  </div>

  <div class="right-panel">
    <div class="form-title">Welcome back</div>
    <div class="form-sub">Sign in or create your patient account</div>

    <div class="tabs">
      <button class="tab-btn <?= $tab==='login'    ? 'active':'' ?>" onclick="switchTab('login')">Sign In</button>
      <button class="tab-btn <?= $tab==='register' ? 'active':'' ?>" onclick="switchTab('register')">Register</button>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error">⚠️ <?= implode('<br>⚠️ ', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    
    <div id="login-section" class="form-section <?= $tab==='login' ? 'active':'' ?>">
      <form method="post" action="">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field">
          <label>Email Address</label>
          <div class="field-wrap">
            <span class="field-icon">✉️</span>
            <input type="email" name="email" placeholder="you@example.com" required />
          </div>
        </div>
        <div class="field">
          <label>Password</label>
          <div class="field-wrap">
            <span class="field-icon">🔒</span>
            <input type="password" name="password" placeholder="••••••••" required />
          </div>
        </div>
        <button class="submit-btn" type="submit">Sign In to MediCare</button>
      </form>
    </div>

    <div id="register-section" class="form-section <?= $tab==='register' ? 'active':'' ?>">
      <form method="post" action="">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="row-2">
          <div class="field">
            <label>First Name</label>
            <div class="field-wrap">
              <span class="field-icon">👤</span>
              <input type="text" name="name" placeholder="John" required />
            </div>
          </div>
          <div class="field">
            <label>Last Name</label>
            <div class="field-wrap">
              <span class="field-icon">👤</span>
              <input type="text" name="lname" placeholder="Doe" />
            </div>
          </div>
        </div>
        <div class="field">
          <label>Email Address</label>
          <div class="field-wrap">
            <span class="field-icon">✉️</span>
            <input type="email" name="email" placeholder="you@example.com" required />
          </div>
        </div>
        <div class="field">
          <label>Phone Number</label>
          <div class="field-wrap phone-row">
            <select class="country-code" name="phone_code">
              <option value="+250">🇷🇼 +250</option>
              <option value="+1">🇺🇸 +1</option>
              <option value="+44">🇬🇧 +44</option>
              <option value="+254">🇰🇪 +254</option>
              <option value="+255">🇹🇿 +255</option>
              <option value="+256">🇺🇬 +256</option>
              <option value="+243">🇨🇩 +243</option>
            </select>
            <input type="tel" class="phone-num" name="phone" placeholder="078 000 0000" required />
          </div>
        </div>
        <div class="field">
          <label>Location / City</label>
          <div class="field-wrap">
            <span class="field-icon">📍</span>
            <input type="text" name="country" placeholder="e.g. Kigali, Rwanda" />
          </div>
        </div>
        <div class="field">
          <label>Password</label>
          <div class="field-wrap">
            <span class="field-icon">🔒</span>
            <input type="password" name="password" placeholder="Min. 8 characters" required />
          </div>
        </div>
        <div class="terms">
          <input type="checkbox" id="terms-check" required />
          <label for="terms-check">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
        </div>
        <button class="submit-btn" type="submit">Create Account</button>
      </form>
    </div>

    <div class="other-logins">
      Are you a doctor? <a href="doctor_login.php">Doctor Login</a> &nbsp;|&nbsp;
      <a href="admin_login.php">Admin Login</a>
    </div>
  </div>
</div>

<script>
function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach((b,i) => {
    b.classList.toggle('active',(i===0 && tab==='login')||(i===1 && tab==='register'));
  });
  document.getElementById('login-section').classList.toggle('active', tab==='login');
  document.getElementById('register-section').classList.toggle('active', tab==='register');
}
</script>
</body>
</html>
