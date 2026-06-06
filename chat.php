<?php
/**
 * chat.php — Patient selects a doctor and starts a video call.
 * Fixed: added auth check, loads divisions from DB, redirects to video_call.php.
 */

require_once 'db.php';
require_patient();

$pdo        = get_pdo();
$patient_id = (int) $_SESSION['patient_id'];

// Load divisions from DB
$divisions = $pdo->query('SELECT id, name, icon FROM divisions ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Talk Live — MediAssist Pro</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Supplement style.css for the picker overlay */
    #pickerOverlay{display:flex;position:fixed;inset:0;background:rgba(4,18,42,.95);z-index:500;align-items:center;justify-content:center;padding:1.5rem}
    #pickerCard{background:#071d3b;border:1px solid rgba(62,207,160,.18);border-radius:1.4rem;padding:2rem;width:100%;max-width:540px;max-height:90vh;overflow-y:auto}
    #pickerCard h2{font-size:1.3rem;font-weight:700;color:#fff;margin-bottom:.3rem}
    #pickerCard > p{color:rgba(226,234,214,.55);font-size:.88rem;margin-bottom:1.4rem}
    .div-tabs{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.2rem}
    .div-tab{padding:.4rem .9rem;border-radius:20px;border:1px solid rgba(255,255,255,.1);background:none;color:rgba(226,234,214,.7);cursor:pointer;font-size:.82rem;transition:.2s}
    .div-tab:hover,.div-tab.active{background:#3ecfa0;border-color:#3ecfa0;color:#04122a;font-weight:600}
    #doctorList{min-height:60px;margin-bottom:1.2rem}
    .doctor-row{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1rem;border-radius:.8rem;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03);cursor:pointer;transition:.2s;margin-bottom:.6rem}
    .doctor-row:hover{border-color:rgba(62,207,160,.3);background:rgba(62,207,160,.06)}
    .doctor-row strong{color:#fff;font-size:.95rem}
    .doctor-row small{color:rgba(226,234,214,.55);font-size:.8rem}
    .dr-badge{padding:.3rem .8rem;background:rgba(62,207,160,.12);border:1px solid rgba(62,207,160,.25);border-radius:6px;color:#5ff5c0;font-size:.78rem;font-weight:600;white-space:nowrap}
    .back-btn{width:100%;padding:.65rem;border-radius:.8rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(226,234,214,.7);cursor:pointer;font-size:.88rem;transition:.2s}
    .back-btn:hover{background:rgba(255,255,255,.1);color:#e2ead6}
    .msg{color:rgba(226,234,214,.5);text-align:center;padding:1.2rem 0;font-size:.85rem}
  </style>
</head>
<body>

<div id="pickerOverlay">
  <div id="pickerCard">
    <h2>👨‍⚕️ Choose a Doctor</h2>
    <p>Select a division, then click a doctor to start a live video consultation.</p>

    <div class="div-tabs" id="divTabs">
      <?php foreach ($divisions as $d): ?>
        <button class="div-tab"
                onclick="selectDiv(<?= $d['id'] ?>, this)">
          <?= htmlspecialchars($d['icon'] ?: '') ?> <?= htmlspecialchars($d['name']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <div id="doctorList">
      <div class="msg">← Select a division above to see available doctors.</div>
    </div>

    <button class="back-btn" onclick="window.location.href='home.php'">← Back to Dashboard</button>
  </div>
</div>

<script>
const PATIENT_ID = <?= $patient_id ?>;

function selectDiv(id, btn) {
  document.querySelectorAll('.div-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadDoctors(id);
}

function loadDoctors(divId) {
  const list = document.getElementById('doctorList');
  list.innerHTML = '<div class="msg">Loading doctors…</div>';

  fetch('get_doctors.php?division_id=' + divId)
    .then(r => r.json())
    .then(data => {
      list.innerHTML = '';
      if (!data.length) {
        list.innerHTML = '<div class="msg">No doctors available in this division.</div>';
        return;
      }
      data.forEach(doc => {
        const row = document.createElement('div');
        row.className = 'doctor-row';
        row.innerHTML =
          '<div>' +
            '<strong>Dr. ' + escHtml(doc.full_name) + '</strong><br>' +
            '<small>' + escHtml(doc.specialty || 'General Practitioner') + '</small>' +
          '</div>' +
          '<div class="dr-badge">📹 Talk Live</div>';
        row.onclick = () => startCall(doc.id);
        list.appendChild(row);
      });
    })
    .catch(() => {
      list.innerHTML = '<div class="msg">Failed to load doctors. Please try again.</div>';
    });
}

function startCall(doctorId) {
  // Redirect to the dedicated video call page which handles call_notify properly
  window.location.href = 'video_call.php?doctor_id=' + doctorId;
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}
</script>
</body>
</html>
