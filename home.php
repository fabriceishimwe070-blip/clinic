<?php
require_once 'db.php';
require_patient();

$pdo     = get_pdo();
$patient = $_SESSION;

$divs = $pdo->query('SELECT * FROM divisions ORDER BY name')->fetchAll();

$apptStmt = $pdo->prepare(
    'SELECT a.*, d.full_name AS doctor_name, dv.name AS division_name
     FROM appointments a
     JOIN doctors   d  ON a.doctor_id   = d.id
     JOIN divisions dv ON a.division_id = dv.id
     WHERE a.patient_id = ?
     ORDER BY a.created_at DESC LIMIT 10'
);
$apptStmt->execute([$patient['patient_id']]);
$myAppts = $apptStmt->fetchAll();
$csrf    = csrf_token();

$total    = count($myAppts);
$pending  = count(array_filter($myAppts, fn($a)=>$a['status']==='pending'));
$accepted = count(array_filter($myAppts, fn($a)=>$a['status']==='accepted'));
$done     = count(array_filter($myAppts, fn($a)=>$a['status']==='completed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediAssist Pro</title>
  <link rel="stylesheet" href="home2.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<header class="topbar">
  <nav class="topbar-nav">
    <a href="chat.php">💬 Live Chat</a>
    <div class="settings-wrap">
      <button onclick="toggleSettings()">⚙️ Settings</button>
      <div class="settings-menu" id="settingsMenu">
        <button>🌙 Dark mode</button>
        <button>🔤 Font size</button>
        <button>❓ Help &amp; Support</button>
      </div>
    </div>
    <button onclick="toggleDivisions()">📂 Divisions</button>
  </nav>
  <div class="profile-chip">
    <div class="av"><?= strtoupper(substr($patient['patient_name'],0,2)) ?></div>
    <span class="pname"><?= htmlspecialchars($patient['patient_name']) ?></span>
  </div>
</header>

<main class="page">

  <?php if (!empty($_GET['appt'])): ?>
    <div class="alert <?= $_GET['appt']==='ok' ? 'alert-ok' : 'alert-err' ?>">
      <?= $_GET['appt']==='ok'
          ? '✅ Appointment submitted! The doctor will review it shortly.'
          : '⚠️ ' . htmlspecialchars($_GET['msg'] ?? 'Error submitting appointment.') ?>
    </div>
  <?php endif; ?>

  <div class="bento">

    

    <div class="card card-profile">
      <div class="big-avatar"><?= strtoupper(substr($patient['patient_name'],0,2)) ?></div>
      <div class="uname"><?= htmlspecialchars($patient['patient_name']) ?></div>
      <div class="urole">Patient Portal</div>
      <div class="action-links">
        <a href="chat.php" class="link-teal">💬 Talk Live</a>
        <a href="logout.php" class="link-red">🚪 Log Out</a>
      </div>
    </div>

    <div class="card card-symptom" >
      <h2>🩺 Describe your symptoms</h2>
      <div class="input-group">
        <input type="text" id="symptomInput" placeholder="e.g. severe headache, fever, nausea …" autocomplete="off">
        <button id="checkBtn">🔍 Check</button>
      </div>
      <div id="resultContainer" class="result-box">
        <div style="color:#5e6f8d;text-align:center;padding:1rem 0;">
          ✨ Enter your symptoms above — our AI will suggest a diagnosis &amp; medicine.
        </div>
      </div>
    </div>

    <div class="card card-stat">
      <div class="stat-grid">
        <div class="stat-item">
          <div class="num"><?= $total ?></div>
          <div class="lbl">Total</div>
        </div>
        <div class="stat-item">
          <div class="num" style="color:var(--gold)"><?= $pending ?></div>
          <div class="lbl">Pending</div>
        </div>
        <div class="stat-item">
          <div class="num" style="color:var(--teal)"><?= $accepted ?></div>
          <div class="lbl">Accepted</div>
        </div>
        <div class="stat-item">
          <div class="num" style="color:#7dd87d"><?= $done ?></div>
          <div class="lbl">Done</div>
        </div>
      </div>
    </div>

    <div class="card card-appt">
      <h3>📅 My Appointments</h3>
      <?php if ($myAppts): ?>
      <table class="appt-table">
        <thead>
          <tr>
            <th>Division</th><th>Doctor</th><th>Date &amp; Time</th><th>Status</th><th>Note</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($myAppts as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['division_name']) ?></td>
            <td><?= htmlspecialchars($a['doctor_name']) ?></td>
            <td><?= htmlspecialchars($a['appt_date'].' '.$a['appt_time']) ?></td>
            <td><span class="badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
            <td><?= $a['doctor_note'] ? htmlspecialchars($a['doctor_note']) : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="empty-state">No appointments yet. Click a division to book one.</p>
      <?php endif; ?>
    </div>

    <div class="card card-divisions" id="card-divisions">
      <div class="divisions-header">📂 Medical Divisions</div>
      <div class="divisions-list" id="divisionsListContainer"></div>
      <div class="divisions-hint">Tap any division to see doctors &amp; book</div>
    </div>

  </div>
</main>

<div id="divisionPopup" class="popup-overlay">
  <div class="popup-card">
    <div class="popup-header">
      <span id="popupTitle">Division</span>
      <button id="closePopupBtn">&times;</button>
    </div>
    <div class="popup-body" id="popupBody">
      <p>Loading division details…</p>
    </div>
  </div>
</div>

<div id="bookingModal" class="modal-overlay">
  <div class="modal-card">
    <h3>📅 Book Appointment</h3>
    <form method="post" action="book_appointment.php">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <label>Division</label>
      <select name="division_id" id="modal-division" required onchange="loadDoctors(this.value)">
        <option value="">— Select Division —</option>
        <?php foreach ($divs as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['icon'].' '.$d['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Doctor</label>
      <select name="doctor_id" id="modal-doctor" required>
        <option value="">— Select Division First —</option>
      </select>
      <label>Date</label>
      <input type="date" name="appt_date" required min="<?= date('Y-m-d') ?>">
      <label>Time</label>
      <input type="time" name="appt_time" required>
      <label>Reason for Visit</label>
      <textarea name="message" placeholder="Describe your symptoms or reason…"></textarea>
      <div class="modal-btns">
        <button type="button" class="btn-cancel" onclick="closeBooking()">Cancel</button>
        <button type="submit" class="btn-teal">Submit Appointment</button>
      </div>
    </form>
  </div>
</div>

<script>
const divisionsData = <?= json_encode(array_map(fn($d)=>[
  'id'=>$d['id'],'name'=>$d['name'],'icon'=>$d['icon'],'description'=>$d['description']
], $divs)) ?>;

(function renderDivisions() {
  const c = document.getElementById('divisionsListContainer');
  divisionsData.forEach(div => {
    const btn = document.createElement('button');
    btn.className = 'division-btn';
    btn.innerHTML = `<span class="division-icon">${div.icon}</span><span>${div.name}</span>`;
    btn.onclick = () => showDivisionPopup(div);
    c.appendChild(btn);
  });
})();

function toggleDivisions() {
    const card = document.getElementById('card-divisions');

    card.style.display =
        card.style.display === 'none' ? 'block' : 'none';

    card.style.marginTop = '100px';
    card.style.width = '400px';
}

function showDivisionPopup(division) {
  document.getElementById('popupTitle').textContent = division.icon + ' ' + division.name;
  const body = document.getElementById('popupBody');
  body.innerHTML = '<p style="color:#9bb0c8">Loading doctors…</p>';
  document.getElementById('divisionPopup').classList.add('active');

  fetch('get_doctors.php?division_id=' + division.id)
    .then(r => r.json())
    .then(doctors => {
      let html = `<p><strong>📋 About:</strong> ${division.description}</p>`;
      if (doctors.length) {
        html += '<p style="margin:.75rem 0 .3rem"><strong>👨‍⚕️ Specialists:</strong></p><ul class="doctor-list">';
        doctors.forEach(d => {
          const talkUrl = `video_call.php?doctor_id=${d.id}`;
          html += `<li style="display:flex;align-items:center;justify-content:space-between;gap:.5rem">
            <span>🩺 <strong>${d.full_name}</strong>${d.specialty ? ' — ' + d.specialty : ''}</span>
            <a href="${talkUrl}" class="btn-teal" style="font-size:.78rem;padding:.3rem .8rem;border-radius:60px;text-decoration:none;white-space:nowrap;background:#0d9488;color:#fff">📹 Video Call</a>
          </li>`;
        });
        html += '</ul>';
      } else {
        html += '<p style="color:#9bb0c8;margin:.7rem 0">No doctors assigned yet.</p>';
      }
      html += `<button class="btn-teal" style="margin-top:1rem;width:100%" onclick="openBooking(${division.id})">📅 Book Appointment</button>`;
      html += `<button class="close-popup" id="innerClose" style="margin-top:.5rem">Close</button>`;
      body.innerHTML = html;
      document.getElementById('innerClose').onclick = () =>
        document.getElementById('divisionPopup').classList.remove('active');
    });
}

function openBooking(divisionId) {
  document.getElementById('divisionPopup').classList.remove('active');
  document.getElementById('modal-division').value = divisionId;
  loadDoctors(divisionId);
  document.getElementById('bookingModal').classList.add('active');
}

function closeBooking() {
  document.getElementById('bookingModal').classList.remove('active');
}

function loadDoctors(divId) {
  const sel = document.getElementById('modal-doctor');
  sel.innerHTML = '<option value="">Loading…</option>';
  if (!divId) { sel.innerHTML = '<option value="">— Select Division First —</option>'; return; }
  fetch('get_doctors.php?division_id=' + divId)
    .then(r => r.json())
    .then(docs => {
      sel.innerHTML = '<option value="">— Choose Doctor —</option>';
      docs.forEach(d => {
        sel.innerHTML += `<option value="${d.id}">${d.full_name}${d.specialty?' ('+d.specialty+')':''}</option>`;
      });
    });
}

function toggleSettings() {
  document.getElementById('settingsMenu').classList.toggle('open');
}

document.getElementById('divisionPopup').onclick = e => {
  if (e.target === e.currentTarget) e.currentTarget.classList.remove('active');
};
document.getElementById('bookingModal').onclick = e => {
  if (e.target === e.currentTarget) closeBooking();
};
document.getElementById('closePopupBtn').onclick = () =>
  document.getElementById('divisionPopup').classList.remove('active');

const diseaseDB = {
  malaria:     {symptoms:["fever","chills","sweating","headache","nausea"],advice:"Rest and hydrate. Seek a doctor — anti-malarials are required.",medicine:"Artemether-Lumefantrine (Coartem)"},
  flu:         {symptoms:["fever","cough","sore throat","runny nose","fatigue"],advice:"Rest, fluids, paracetamol for fever.",medicine:"Oseltamivir (Tamiflu) if severe"},
  hypertension:{symptoms:["headache","dizziness","chest pain","shortness of breath"],advice:"Reduce salt, exercise regularly, monitor blood pressure.",medicine:"Amlodipine / Lisinopril (consult doctor)"},
  diabetes:    {symptoms:["frequent urination","thirst","fatigue","blurry vision"],advice:"Diet control and monitor blood sugar closely.",medicine:"Metformin (Type 2) · Insulin (Type 1)"},
  pneumonia:   {symptoms:["cough","fever","chest pain","shortness of breath","fatigue"],advice:"See a doctor urgently. Rest and take prescribed antibiotics.",medicine:"Amoxicillin / Azithromycin"},
  migraine:    {symptoms:["headache","nausea","light sensitivity","vomiting"],advice:"Rest in a dark quiet room, stay hydrated.",medicine:"Ibuprofen / Sumatriptan"},
  anemia:      {symptoms:["fatigue","pale skin","shortness of breath","dizziness"],advice:"Eat iron-rich foods and take folic acid supplements.",medicine:"Ferrous Sulfate / Folic Acid"},
};

document.getElementById('checkBtn').onclick = () => {
  const input = document.getElementById('symptomInput').value.toLowerCase();
  const box   = document.getElementById('resultContainer');
  if (!input.trim()) {
    box.innerHTML = '<div style="color:#f87171;padding:.8rem">Please enter your symptoms.</div>'; return;
  }
  let best = null, bestScore = 0;
  for (const [disease, data] of Object.entries(diseaseDB)) {
    const score = data.symptoms.filter(s => input.includes(s)).length;
    if (score > bestScore) { bestScore = score; best = {disease, ...data}; }
  }
  if (best && bestScore >= 2) {
    box.innerHTML =
      `<div class="disease-name">🔬 Possible: ${best.disease.charAt(0).toUpperCase()+best.disease.slice(1)}</div>
       <div class="advice">💡 ${best.advice}</div>
       <div class="medicine">💊 ${best.medicine}</div>
       <div class="note">📌 AI-assisted suggestion — always consult a qualified doctor.</div>`;
  } else {
    box.innerHTML =
      '<div class="disease-name">🔍 No clear match</div>' +
      '<div class="advice">Try refining your symptoms, or use the divisions panel to contact a specialist.</div>';
  }
};
</script>
</body>
</html>
