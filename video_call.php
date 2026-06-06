<?php
require_once 'db.php';
require_patient();

$pdo        = get_pdo();
$patient_id = (int)$_SESSION['patient_id'];
$patient_name = htmlspecialchars($_SESSION['patient_name'] ?? 'Patient');

$doctor_id = (int)($_GET['doctor_id'] ?? 0);
if (!$doctor_id) { header('Location: home.php'); exit; }

// Fetch doctor info
$stmt = $pdo->prepare('SELECT id, full_name, specialty, email, phone FROM doctors WHERE id=?');
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();
if (!$doctor) { header('Location: home.php'); exit; }

// Generate a unique Jitsi room name
$room_name = 'doctor-' . $doctor_id . '-patient-' . $patient_id;
$room_url  = 'https://meet.jit.si/' . $room_name;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Video Call — <?= htmlspecialchars($doctor['full_name']) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');
    :root {
      --bg: #04122a; --navy2: #071d3b; --teal: #3ecfa0; --teal-glow: #5ff5c0;
      --gold: #e8b84b; --cream: #e2ead6; --cream-dim: rgba(226,234,214,.55);
      --error: #f87171; --border: rgba(255,255,255,.09);
    }
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--cream); min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:1.5rem; }

    /* ── Header ── */
    .topbar { width:100%; max-width:700px; display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; }
    .brand { font-size:1.1rem; font-weight:700; color:var(--teal); }
    .back-btn { padding:.4rem .9rem; border-radius:8px; background:rgba(255,255,255,.06); border:1px solid var(--border); color:var(--cream-dim); text-decoration:none; font-size:.83rem; transition:.2s; }
    .back-btn:hover { background:rgba(255,255,255,.1); }

    /* ── Call card ── */
    .call-card { background:var(--navy2); border:1px solid var(--border); border-radius:1.4rem; padding:2.5rem 2rem; width:100%; max-width:500px; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.4); }
    .doctor-avatar { width:90px; height:90px; border-radius:50%; background:linear-gradient(135deg,rgba(62,207,160,.25),rgba(232,184,75,.2)); display:flex; align-items:center; justify-content:center; font-size:2.6rem; margin:0 auto 1.2rem; border:2px solid rgba(62,207,160,.3); }
    .doctor-name { font-size:1.4rem; font-weight:700; color:#fff; margin-bottom:.3rem; }
    .doctor-spec { font-size:.85rem; color:var(--teal-glow); margin-bottom:.2rem; }
    .doctor-email { font-size:.78rem; color:var(--cream-dim); margin-bottom:1.8rem; }

    /* Status pill */
    .status-pill { display:inline-flex; align-items:center; gap:.45rem; padding:.45rem 1rem; border-radius:50px; font-size:.83rem; font-weight:600; margin-bottom:1.5rem; transition:.3s; }
    .status-idle    { background:rgba(255,255,255,.06); border:1px solid var(--border); color:var(--cream-dim); }
    .status-ringing { background:rgba(62,207,160,.12); border:1px solid rgba(62,207,160,.35); color:var(--teal-glow); animation:pulseStatus 1.2s ease-in-out infinite; }
    .status-answered{ background:rgba(100,200,100,.12); border:1px solid rgba(100,200,100,.35); color:#7dd87d; }
    .status-declined{ background:rgba(248,113,113,.12); border:1px solid rgba(248,113,113,.3); color:var(--error); }
    .status-missed  { background:rgba(232,184,75,.12); border:1px solid rgba(232,184,75,.3); color:var(--gold); }
    @keyframes pulseStatus { 0%,100%{opacity:1} 50%{opacity:.6} }

    /* Ring animation ring */
    .ring-anim { display:none; position:relative; width:110px; height:110px; margin:0 auto 1.5rem; }
    .ring-anim.active { display:block; }
    .ring-anim .avatar-wrap { position:absolute; inset:15px; border-radius:50%; background:linear-gradient(135deg,rgba(62,207,160,.3),rgba(232,184,75,.2)); display:flex; align-items:center; justify-content:center; font-size:2.5rem; border:2px solid rgba(62,207,160,.4); z-index:2; }
    .ring-wave { position:absolute; inset:0; border-radius:50%; border:2px solid rgba(62,207,160,.5); animation:ringWave 1.6s ease-out infinite; }
    .ring-wave:nth-child(2) { animation-delay:.5s; }
    .ring-wave:nth-child(3) { animation-delay:1s; }
    @keyframes ringWave { 0%{transform:scale(.8);opacity:.8} 100%{transform:scale(1.5);opacity:0} }

    /* Buttons */
    .btn-row { display:flex; gap:.8rem; justify-content:center; margin-top:.5rem; flex-wrap:wrap; }
    .btn { padding:.75rem 1.8rem; border-radius:10px; border:none; font-size:.95rem; font-weight:700; cursor:pointer; transition:.2s; display:flex; align-items:center; gap:.5rem; }
    .btn-call { background:#16a34a; color:#fff; }
    .btn-call:hover { background:#15803d; transform:scale(1.04); }
    .btn-hangup { background:rgba(248,113,113,.2); border:1px solid rgba(248,113,113,.35); color:var(--error); }
    .btn-hangup:hover { background:rgba(248,113,113,.32); }
    .btn-join { background:var(--teal); color:#04122a; }
    .btn-join:hover { background:#5ff5c0; }
    .btn-new { background:rgba(62,207,160,.12); border:1px solid rgba(62,207,160,.3); color:var(--teal-glow); }
    .btn-new:hover { background:rgba(62,207,160,.22); }
    .btn[disabled] { opacity:.4; cursor:not-allowed; transform:none !important; }

    .room-link-box { margin-top:1.2rem; background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:.8rem; padding:.75rem 1rem; font-size:.78rem; color:var(--cream-dim); word-break:break-all; display:none; }
    .room-link-box.show { display:block; }
    .room-link-box a { color:var(--teal-glow); text-decoration:none; }
    .room-link-box a:hover { text-decoration:underline; }

    .timer { font-size:.82rem; color:var(--cream-dim); margin-top:.8rem; display:none; }
    .timer.show { display:block; }
    
    .info-note { margin-top:1.5rem; font-size:.78rem; color:var(--cream-dim); line-height:1.5; padding:.7rem 1rem; background:rgba(255,255,255,.03); border-radius:.7rem; border:1px solid var(--border); }
  </style>
</head>
<body>

<div class="topbar">
  <div class="brand">🏥 FClinic</div>
  <a href="home.php" class="back-btn">← Back to Dashboard</a>
</div>

<div class="call-card">
  <!-- Ringing animation (shown when calling) -->
  <div class="ring-anim" id="ringAnim">
    <div class="ring-wave"></div>
    <div class="ring-wave"></div>
    <div class="ring-wave"></div>
    <div class="avatar-wrap">👨‍⚕️</div>
  </div>

  <!-- Static avatar (shown when idle) -->
  <div class="doctor-avatar" id="staticAvatar">👨‍⚕️</div>

  <div class="doctor-name">Dr. <?= htmlspecialchars($doctor['full_name']) ?></div>
  <div class="doctor-spec"><?= htmlspecialchars($doctor['specialty'] ?: 'General Practitioner') ?></div>
  <div class="doctor-email">📧 <?= htmlspecialchars($doctor['email']) ?></div>

  <div class="status-pill status-idle" id="statusPill">
    <span id="statusDot">⚪</span>
    <span id="statusText">Ready to call</span>
  </div>

  <div class="btn-row" id="btnRow">
    <button class="btn btn-call" id="btnStartCall" onclick="startCall()">
      📞 Call Doctor
    </button>
  </div>

  <div class="room-link-box" id="roomBox">
    📹 Video Room: <a id="roomLinkA" href="#" target="_blank" rel="noopener"></a>
  </div>

  <div class="timer" id="timerBox">⏱ <span id="timerText">Calling…</span></div>

  <div class="info-note">
    💡 When the doctor answers, the video room will open automatically in a new tab. 
    Make sure your browser allows pop-ups for this site.
  </div>
</div>

<script>
const DOCTOR_ID  = <?= $doctor_id ?>;
const ROOM_URL   = <?= json_encode($room_url) ?>;
const DOCTOR_NAME = <?= json_encode($doctor['full_name']) ?>;

let callId       = null;
let callState    = 'idle';   // idle | ringing | answered | declined | missed
let pollTimer    = null;
let ringBeepTimer= null;
let elapsedSecs  = 0;
let elapsedTimer = null;
let ringAudio    = null;

// --- DOM refs
const ringAnim   = document.getElementById('ringAnim');
const staticAv   = document.getElementById('staticAvatar');
const statusPill = document.getElementById('statusPill');
const statusDot  = document.getElementById('statusDot');
const statusTxt  = document.getElementById('statusText');
const btnRow     = document.getElementById('btnRow');
const roomBox    = document.getElementById('roomBox');
const roomLinkA  = document.getElementById('roomLinkA');
const timerBox   = document.getElementById('timerBox');
const timerTxt   = document.getElementById('timerText');

function setStatus(state, label, dotEmoji) {
  statusPill.className = 'status-pill status-' + state;
  statusDot.textContent = dotEmoji;
  statusTxt.textContent = label;
}

function startElapsed() {
  elapsedSecs = 0;
  elapsedTimer = setInterval(() => {
    elapsedSecs++;
    const m = String(Math.floor(elapsedSecs / 60)).padStart(2,'0');
    const s = String(elapsedSecs % 60).padStart(2,'0');
    timerTxt.textContent = `Ringing… ${m}:${s}`;
  }, 1000);
  timerBox.classList.add('show');
}

function stopElapsed() {
  clearInterval(elapsedTimer);
  timerBox.classList.remove('show');
}

// Web Audio beep
let audioCtx = null;
function beep() {
  try {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const o = audioCtx.createOscillator();
    const g = audioCtx.createGain();
    o.connect(g); g.connect(audioCtx.destination);
    o.frequency.value = 440;
    g.gain.setValueAtTime(0.25, audioCtx.currentTime);
    g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.4);
    o.start(); o.stop(audioCtx.currentTime + 0.4);
  } catch(e) {}
}

function startRing() { ringBeepTimer = setInterval(beep, 1600); beep(); }
function stopRing()  { clearInterval(ringBeepTimer); }

async function startCall() {
  try {
    const r = await fetch('call_notify.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        doctor_id: DOCTOR_ID,
        room_url: ROOM_URL
      })
    });

    const d = await r.json();

    if (!d.ok) {
      alert('Could not reach the server. Please try again.');
      return;
    }

    callId = d.call_id;

    window.open(
      ROOM_URL + '#config.startWithVideoMuted=false',
      '_blank'
    );

  } catch (e) {
    alert('Network error. Please check your connection.');
    return;
  }

  callState = 'ringing';

  staticAv.style.display = 'none';

  ringAnim.classList.add('active');

  setStatus(
    'ringing',
    '🔔 Ringing…',
    '🟢'
  );

  roomBox.classList.add('show');

  roomLinkA.href = ROOM_URL;

  roomLinkA.textContent = ROOM_URL;

  btnRow.innerHTML = `
    <button class="btn btn-join" onclick="joinRoom()">
      📹 Join Room Now
    </button>

    <button class="btn btn-hangup" onclick="hangUp()">
      📵 Hang Up
    </button>
  `;

  startElapsed();

  startRing();

  pollTimer = setInterval(
    pollStatus,
    2000
  );
}

async function pollStatus() {
  if (!callId) return;
  try {
    const r = await fetch('call_status.php?call_id=' + callId);
    const d = await r.json();
    if (!d.ok) return;

    const s = d.status;
    if (s === 'answered') {
      clearInterval(pollTimer);
      stopRing(); stopElapsed();
      callState = 'answered';
      ringAnim.classList.remove('active');
      staticAv.style.display = '';
      setStatus('answered','✅ Doctor answered!','🟢');
      timerBox.classList.add('show');
      timerTxt.textContent = '🎉 Doctor is in the room!';
      btnRow.innerHTML = `
        <button class="btn btn-join" onclick="joinRoom()">📹 Join Room</button>
        <button class="btn btn-hangup" onclick="resetCall()">🔚 End</button>
      `;
      // Auto-open room
      window.open(ROOM_URL + '#config.startWithVideoMuted=false', '_blank');

    } else if (s === 'declined') {
      clearInterval(pollTimer);
      stopRing(); stopElapsed();
      callState = 'declined';
      ringAnim.classList.remove('active');
      staticAv.style.display = '';
      setStatus('declined','❌ Doctor declined','🔴');
      timerBox.classList.add('show');
      timerTxt.textContent = 'The doctor is currently unavailable.';
      btnRow.innerHTML = `<button class="btn btn-new" onclick="resetCall()">🔄 Try Again</button>`;

    } else if (s === 'missed') {
      // Still ringing, not missed yet unless server already marked it
      clearInterval(pollTimer);
      stopRing(); stopElapsed();
      callState = 'missed';
      ringAnim.classList.remove('active');
      staticAv.style.display = '';
      setStatus('missed','📵 No answer','🟡');
      timerBox.classList.add('show');
      timerTxt.textContent = 'Doctor did not answer. Try again later.';
      btnRow.innerHTML = `<button class="btn btn-new" onclick="resetCall()">🔄 Call Again</button>`;
    }
  } catch(e) {}
}

async function hangUp() {
  clearInterval(pollTimer);
  stopRing(); stopElapsed();
  // Mark as missed in DB
  if (callId) {
    fetch('call_status.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ call_id: callId, action: 'cancel' })
    }).catch(()=>{});
  }
  callState = 'missed';
  ringAnim.classList.remove('active');
  staticAv.style.display = '';
  setStatus('missed','📵 Call ended','🟡');
  timerBox.classList.add('show');
  timerTxt.textContent = 'You ended the call.';
  btnRow.innerHTML = `
    <button class="btn btn-new" onclick="resetCall()">🔄 Call Again</button>
    <a href="home.php" class="btn btn-hangup" style="text-decoration:none">🏠 Dashboard</a>
  `;
}

function joinRoom() {
  window.open(ROOM_URL + '#config.startWithVideoMuted=false', '_blank');
}

function resetCall() {
  callId = null; callState = 'idle';
  ringAnim.classList.remove('active');
  staticAv.style.display = '';
  roomBox.classList.remove('show');
  timerBox.classList.remove('show');
  setStatus('idle','Ready to call','⚪');
  btnRow.innerHTML = `<button class="btn btn-call" id="btnStartCall" onclick="startCall()">📞 Call Doctor</button>`;
}
</script>
</body>
</html>
