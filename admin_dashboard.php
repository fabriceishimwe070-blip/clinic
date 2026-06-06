<?php
// ── admin_dashboard.php ── Full Admin Control Panel
require_once 'db.php';
require_admin();

$pdo = get_pdo();
$msg = '';
$err = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

   
    if ($action === 'add_division') {
        $name = trim($_POST['div_name'] ?? '');
        $icon = trim($_POST['div_icon'] ?? '🏥');
        $desc = trim($_POST['div_desc'] ?? '');
        if (!$name) { $err = 'Division name is required.'; }
        else {
            try {
                $pdo->prepare('INSERT INTO divisions (name,icon,description) VALUES (?,?,?)')
                    ->execute([$name,$icon,$desc]);
                $msg = "Division '$name' created.";
            } catch (PDOException $e) {
                $err = 'Division name already exists.';
            }
        }
    }

    
    elseif ($action === 'edit_division') {
        $id   = filter_input(INPUT_POST,'div_id',FILTER_VALIDATE_INT);
        $name = trim($_POST['div_name'] ?? '');
        $icon = trim($_POST['div_icon'] ?? '🏥');
        $desc = trim($_POST['div_desc'] ?? '');
        if ($id && $name) {
            $pdo->prepare('UPDATE divisions SET name=?,icon=?,description=? WHERE id=?')
                ->execute([$name,$icon,$desc,$id]);
            $msg = 'Division updated.';
        }
    }

   
    elseif ($action === 'delete_division') {
        $id = filter_input(INPUT_POST,'div_id',FILTER_VALIDATE_INT);
        if ($id) {
            $pdo->prepare('DELETE FROM divisions WHERE id=?')->execute([$id]);
            $msg = 'Division deleted.';
        }
    }

    
    elseif ($action === 'add_doctor') {
        $name      = trim($_POST['doc_name']      ?? '');
        $email     = trim($_POST['doc_email']     ?? '');
        $specialty = trim($_POST['doc_specialty'] ?? '');
        $phone     = trim($_POST['doc_phone']     ?? '');
        $bio       = trim($_POST['doc_bio']       ?? '');
        $password  = $_POST['doc_password']       ?? '';

        if (!$name || !$email || !$password) {
            $err = 'Name, email, and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Invalid email address.';
        } elseif (strlen($password) < 8) {
            $err = 'Password must be at least 8 characters.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare(
                    'INSERT INTO doctors (full_name,email,password,specialty,phone,bio) VALUES (?,?,?,?,?,?)'
                )->execute([$name,$email,$hash,$specialty,$phone,$bio]);
                $msg = "Doctor '$name' added.";
            } catch (PDOException $e) {
                $err = 'Email already registered.';
            }
        }
    }

   
    elseif ($action === 'edit_doctor') {
        $id        = filter_input(INPUT_POST,'doc_id',FILTER_VALIDATE_INT);
        $name      = trim($_POST['doc_name']      ?? '');
        $email     = trim($_POST['doc_email']     ?? '');
        $specialty = trim($_POST['doc_specialty'] ?? '');
        $phone     = trim($_POST['doc_phone']     ?? '');
        $bio       = trim($_POST['doc_bio']       ?? '');
        $password  = $_POST['doc_password']       ?? '';
        if ($id && $name && $email) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare('UPDATE doctors SET full_name=?,email=?,password=?,specialty=?,phone=?,bio=? WHERE id=?')
                    ->execute([$name,$email,$hash,$specialty,$phone,$bio,$id]);
            } else {
                $pdo->prepare('UPDATE doctors SET full_name=?,email=?,specialty=?,phone=?,bio=? WHERE id=?')
                    ->execute([$name,$email,$specialty,$phone,$bio,$id]);
            }
            $msg = 'Doctor updated.';
        }
    }

   
    elseif ($action === 'delete_doctor') {
        $id = filter_input(INPUT_POST,'doc_id',FILTER_VALIDATE_INT);
        if ($id) {
            $pdo->prepare('DELETE FROM doctors WHERE id=?')->execute([$id]);
            $msg = 'Doctor removed.';
        }
    }

    
    elseif ($action === 'assign_doctor') {
        $doc_id = filter_input(INPUT_POST,'assign_doc_id',FILTER_VALIDATE_INT);
        $div_id = filter_input(INPUT_POST,'assign_div_id',FILTER_VALIDATE_INT);
        if ($doc_id && $div_id) {
            try {
                $pdo->prepare('INSERT INTO doctor_divisions (doctor_id,division_id) VALUES (?,?)')
                    ->execute([$doc_id,$div_id]);
                $msg = 'Doctor assigned to division.';
            } catch (PDOException $e) {
                $err = 'This doctor is already assigned to that division.';
            }
        }
    }

   
    elseif ($action === 'remove_assignment') {
        $doc_id = filter_input(INPUT_POST,'assign_doc_id',FILTER_VALIDATE_INT);
        $div_id = filter_input(INPUT_POST,'assign_div_id',FILTER_VALIDATE_INT);
        if ($doc_id && $div_id) {
            $pdo->prepare('DELETE FROM doctor_divisions WHERE doctor_id=? AND division_id=?')
                ->execute([$doc_id,$div_id]);
            $msg = 'Assignment removed.';
        }
    }

    header('Location: admin_dashboard.php?msg='.urlencode($msg).'&err='.urlencode($err));
    exit;
}

// Pass-through flash messages
if (isset($_GET['msg'])) $msg = $_GET['msg'];
if (isset($_GET['err'])) $err = $_GET['err'];


$divisions   = $pdo->query('SELECT * FROM divisions ORDER BY name')->fetchAll();
$doctors     = $pdo->query('SELECT * FROM doctors ORDER BY full_name')->fetchAll();
$assignments = $pdo->query(
    'SELECT dd.*, d.full_name, dv.name AS div_name
     FROM doctor_divisions dd
     JOIN doctors d ON dd.doctor_id=d.id
     JOIN divisions dv ON dd.division_id=dv.id
     ORDER BY dv.name, d.full_name'
)->fetchAll();


$apptStats = $pdo->query(
    'SELECT status, COUNT(*) AS cnt FROM appointments GROUP BY status'
)->fetchAll(PDO::FETCH_KEY_PAIR);

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — MediCare</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
 <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="topbar">
  <div class="topbar-brand">🛡️ MediCare Admin Panel</div>
  <div style="display:flex;align-items:center;gap:1rem;font-size:.9rem"flex-direc>
    <span>👤 <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
    <a href="logout.php?role=admin" class="logout-btn">Sign Out</a>
  </div>
</div>

<div class="main">

  <?php if ($msg): ?><div class="alert-msg alert-success">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert-msg alert-error">⚠️ <?= htmlspecialchars($err) ?></div><?php endif; ?>

  
  <div class="stats">
    <div class="stat-card"><div class="stat-num"><?= count($divisions) ?></div><div class="stat-label">Divisions</div></div>
    <div class="stat-card"><div class="stat-num"><?= count($doctors) ?></div><div class="stat-label">Doctors</div></div>
    <div class="stat-card"><div class="stat-num" style="color:var(--gold)"><?= $apptStats['pending'] ?? 0 ?></div><div class="stat-label">Pending Appts</div></div>
    <div class="stat-card"><div class="stat-num"><?= array_sum($apptStats) ?></div><div class="stat-label">Total Appts</div></div>
  </div>

  
  <div class="tabs">
    <button class="tab active" onclick="showTab('divisions',this)">📂 Divisions</button>
    <button class="tab" onclick="showTab('doctors',this)">👨‍⚕️ Doctors</button>
    <button class="tab" onclick="showTab('assignments',this)">🔗 Assignments</button>
  </div>

  
  <div class="tab-content active" id="tab-divisions">
    
    <div class="section">
      <div class="section-title">➕ Add New Division</div>
      <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="add_division">
        <div class="grid3" style="margin-bottom:.8rem">
          <div class="form-field"><label>Name *</label><input type="text" name="div_name" placeholder="e.g. Cardiology" required></div>
          <div class="form-field"><label>Icon (emoji)</label><input type="text" name="div_icon" placeholder="❤️" maxlength="8"></div>
          <div class="form-field"><label>Description</label><input type="text" name="div_desc" placeholder="Short description"></div>
        </div>
        <button class="btn btn-gold" type="submit">Add Division</button>
      </form>
    </div>
 
    <div class="section">
      <div class="section-title">📋 All Divisions</div>
      <table>
        <thead><tr><th>#</th><th>Icon</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($divisions as $d): ?>
          <tr>
            <td><?= $d['id'] ?></td>
            <td style="font-size:1.3rem"><?= htmlspecialchars($d['icon']) ?></td>
            <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
            <td style="color:var(--cream-dim)"><?= htmlspecialchars($d['description']) ?></td>
            <td>
              <button class="btn btn-teal btn-sm" onclick="editDiv(<?= $d['id'] ?>,'<?= addslashes($d['name']) ?>','<?= addslashes($d['icon']) ?>','<?= addslashes($d['description']) ?>')">Edit</button>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this division?')">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="delete_division">
                <input type="hidden" name="div_id" value="<?= $d['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  
  <div class="tab-content" id="tab-doctors">
    <!-- Add Doctor -->
    <div class="section">
      <div class="section-title">➕ Add New Doctor</div>
      <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="add_doctor">
        <div class="grid2" style="margin-bottom:.8rem">
          <div class="form-field"><label>Full Name *</label><input type="text" name="doc_name" required></div>
          <div class="form-field"><label>Email *</label><input type="email" name="doc_email" required></div>
          <div class="form-field"><label>Password * (min 8 chars)</label><input type="password" name="doc_password" required minlength="8"></div>
          <div class="form-field"><label>Specialty</label><input type="text" name="doc_specialty" placeholder="e.g. Cardiologist"></div>
          <div class="form-field"><label>Phone</label><input type="tel" name="doc_phone"></div>
          <div class="form-field"><label>Bio</label><textarea name="doc_bio" placeholder="Short biography…"></textarea></div>
        </div>
        <button class="btn btn-gold" type="submit">Add Doctor</button>
      </form>
    </div>
    <!-- Doctors Table -->
    <div class="section">
      <div class="section-title">👨‍⚕️ All Doctors</div>
      <table>
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Specialty</th><th>Phone</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($doctors as $d): ?>
          <tr>
            <td><?= $d['id'] ?></td>
            <td><strong><?= htmlspecialchars($d['full_name']) ?></strong></td>
            <td><?= htmlspecialchars($d['email']) ?></td>
            <td><?= htmlspecialchars($d['specialty']) ?></td>
            <td><?= htmlspecialchars($d['phone']) ?></td>
            <td>
              <button class="btn btn-teal btn-sm" onclick="editDoc(<?= $d['id'] ?>,'<?= addslashes($d['full_name']) ?>','<?= addslashes($d['email']) ?>','<?= addslashes($d['specialty']) ?>','<?= addslashes($d['phone']) ?>','<?= addslashes($d['bio']) ?>')">Edit</button>
              <form method="post" style="display:inline" onsubmit="return confirm('Remove this doctor?')">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="delete_doctor">
                <input type="hidden" name="doc_id" value="<?= $d['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>


  <div class="tab-content" id="tab-assignments">
    <!-- Assign Form -->
    <div class="section">
      <div class="section-title">🔗 Assign Doctor to Division</div>
      <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="assign_doctor">
        <div class="grid2" style="margin-bottom:.8rem">
          <div class="form-field">
            <label>Doctor</label>
            <select name="assign_doc_id" required>
              <option value="">— Select Doctor —</option>
              <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field">
            <label>Division</label>
            <select name="assign_div_id" required>
              <option value="">— Select Division —</option>
              <?php foreach ($divisions as $d): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['icon'].' '.$d['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <button class="btn btn-gold" type="submit">Assign</button>
      </form>
    </div>
    <!-- Current Assignments -->
    <div class="section">
      <div class="section-title">📋 Current Assignments</div>
      <table>
        <thead><tr><th>Division</th><th>Doctor</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if ($assignments): foreach ($assignments as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['div_name']) ?></td>
            <td><?= htmlspecialchars($a['full_name']) ?></td>
            <td>
              <form method="post" style="display:inline" onsubmit="return confirm('Remove assignment?')">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="remove_assignment">
                <input type="hidden" name="assign_doc_id" value="<?= $a['doctor_id'] ?>">
                <input type="hidden" name="assign_div_id" value="<?= $a['division_id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="3" style="color:var(--cream-dim);text-align:center;padding:1rem">No assignments yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>


<div id="editDivModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9000;align-items:center;justify-content:center">
  <div style="background:#0d1f38;border:1px solid rgba(232,184,75,.2);border-radius:1rem;padding:2rem;width:90%;max-width:500px">
    <h3 style="color:var(--gold);margin-bottom:1.2rem">✏️ Edit Division</h3>
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="edit_division">
      <input type="hidden" name="div_id" id="edit_div_id">
      <div class="form-field" style="margin-bottom:.8rem"><label>Name</label><input type="text" name="div_name" id="edit_div_name" required></div>
      <div class="form-field" style="margin-bottom:.8rem"><label>Icon</label><input type="text" name="div_icon" id="edit_div_icon"></div>
      <div class="form-field" style="margin-bottom:1.2rem"><label>Description</label><textarea name="div_desc" id="edit_div_desc"></textarea></div>
      <div style="display:flex;gap:.8rem">
        <button class="btn btn-gold" type="submit">Save Changes</button>
        <button class="btn" type="button" onclick="document.getElementById('editDivModal').style.display='none'" style="background:rgba(255,255,255,.07);color:var(--cream)">Cancel</button>
      </div>
    </form>
  </div>
</div>


<div id="editDocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9000;align-items:center;justify-content:center">
  <div style="background:#0d1f38;border:1px solid rgba(232,184,75,.2);border-radius:1rem;padding:2rem;width:90%;max-width:500px;max-height:90vh;overflow-y:auto">
    <h3 style="color:var(--gold);margin-bottom:1.2rem">✏️ Edit Doctor</h3>
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="edit_doctor">
      <input type="hidden" name="doc_id" id="edit_doc_id">
      <div class="grid2" style="margin-bottom:.8rem">
        <div class="form-field"><label>Full Name</label><input type="text" name="doc_name" id="edit_doc_name" required></div>
        <div class="form-field"><label>Email</label><input type="email" name="doc_email" id="edit_doc_email" required></div>
        <div class="form-field"><label>New Password (leave blank = no change)</label><input type="password" name="doc_password" minlength="8"></div>
        <div class="form-field"><label>Specialty</label><input type="text" name="doc_specialty" id="edit_doc_specialty"></div>
        <div class="form-field"><label>Phone</label><input type="tel" name="doc_phone" id="edit_doc_phone"></div>
      </div>
      <div class="form-field" style="margin-bottom:1.2rem"><label>Bio</label><textarea name="doc_bio" id="edit_doc_bio"></textarea></div>
      <div style="display:flex;gap:.8rem">
        <button class="btn btn-gold" type="submit">Save Changes</button>
        <button class="btn" type="button" onclick="document.getElementById('editDocModal').style.display='none'" style="background:rgba(255,255,255,.07);color:var(--cream)">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function showTab(id, btn) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  btn.classList.add('active');
}
function editDiv(id, name, icon, desc) {
  document.getElementById('edit_div_id').value   = id;
  document.getElementById('edit_div_name').value = name;
  document.getElementById('edit_div_icon').value = icon;
  document.getElementById('edit_div_desc').value = desc;
  document.getElementById('editDivModal').style.display = 'flex';
}
function editDoc(id, name, email, spec, phone, bio) {
  document.getElementById('edit_doc_id').value        = id;
  document.getElementById('edit_doc_name').value      = name;
  document.getElementById('edit_doc_email').value     = email;
  document.getElementById('edit_doc_specialty').value = spec;
  document.getElementById('edit_doc_phone').value     = phone;
  document.getElementById('edit_doc_bio').value       = bio;
  document.getElementById('editDocModal').style.display = 'flex';
}

['editDivModal','editDocModal'].forEach(id => {
  document.getElementById(id).addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });
});
</script>
</body>
</html>
