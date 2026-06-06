<?php
/**
 * doctor_dashboard.php
 * Fixed: removed duplicate call polling script, added call history tab,
 * fixed patient_email/phone in call overlay, standardized session handling.
 */

require_once 'db.php';
require_doctor();

$pdo       = get_pdo();
$doctor_id = (int) $_SESSION['doctor_id'];

// ── Handle appointment status update ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    csrf_verify();
    $appt_id     = filter_input(INPUT_POST, 'appt_id',    FILTER_VALIDATE_INT);
    $new_status  = $_POST['new_status']  ?? '';
    $doctor_note = trim($_POST['doctor_note'] ?? '');

    $allowed = ['accepted', 'rejected', 'completed'];
    if ($appt_id && in_array($new_status, $allowed, true)) {
        $chk = $pdo->prepare('SELECT id FROM appointments WHERE id=? AND doctor_id=?');
        $chk->execute([$appt_id, $doctor_id]);
        if ($chk->fetch()) {
            $pdo->prepare(
                'UPDATE appointments SET status=?, doctor_note=? WHERE id=?'
            )->execute([$new_status, $doctor_note, $appt_id]);
            $pdo->prepare('UPDATE notifications SET is_read=1 WHERE appt_id=?')->execute([$appt_id]);
        }
    }
    header('Location: doctor_dashboard.php');
    exit;
}

// ── Unread notification count ────────────────────────────────────────────────
$unreadCount = (int) $pdo->prepare(
    'SELECT COUNT(*) FROM notifications WHERE doctor_id=? AND is_read=0'
)->execute([$doctor_id]) ? $pdo->prepare(
    'SELECT COUNT(*) FROM notifications WHERE doctor_id=? AND is_read=0'
)->execute([$doctor_id]) : 0;

// Simpler approach:
$notifStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE doctor_id=? AND is_read=0');
$notifStmt->execute([$doctor_id]);
$unreadCount = (int) $notifStmt->fetchColumn();

// ── Appointments ─────────────────────────────────────────────────────────────
$apptStmt = $pdo->prepare(
    'SELECT a.*, u.Fname AS patient_name, u.email AS patient_email, u.phon AS patient_phone,
            u.country AS patient_city, dv.name AS division_name,
            n.is_read
     FROM appointments a
     JOIN users      u  ON a.patient_id   = u.id
     JOIN divisions  dv ON a.division_id  = dv.id
     LEFT JOIN notifications n ON n.appt_id = a.id AND n.doctor_id = ?
     WHERE a.doctor_id = ?
     ORDER BY a.created_at DESC'
);
$apptStmt->execute([$doctor_id, $doctor_id]);
$appointments = $apptStmt->fetchAll();

// Mark all notifications as read now that doctor has viewed dashboard
$pdo->prepare('UPDATE notifications SET is_read=1 WHERE doctor_id=?')->execute([$doctor_id]);

$total     = count($appointments);
$pending   = count(array_filter($appointments, fn($a) => $a['status'] === 'pending'));
$accepted  = count(array_filter($appointments, fn($a) => $a['status'] === 'accepted'));
$completed = count(array_filter($appointments, fn($a) => $a['status'] === 'completed'));

// ── Call history (all calls, most recent 50) ──────────────────────────────────
$callHistStmt = $pdo->prepare("
    SELECT
        cr.id,
        cr.room_url,
        cr.status,
        cr.created_at,
        u.Fname  AS patient_name,
        u.email  AS patient_email,
        u.phon   AS patient_phone
    FROM call_requests cr
    JOIN users u ON cr.patient_id = u.id
    WHERE cr.doctor_id = ?
    ORDER BY cr.created_at DESC
    LIMIT 50
");
$callHistStmt->execute([$doctor_id]);
$callHistory = $callHistStmt->fetchAll();

$callRinging  = count(array_filter($callHistory, fn($c) => $c['status'] === 'ringing'));
$callAnswered = count(array_filter($callHistory, fn($c) => $c['status'] === 'answered'));
$callMissed   = count(array_filter($callHistory, fn($c) => in_array($c['status'], ['missed','declined'])));

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Dashboard — MediCare</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    :root{
      --bg:#04122a;--navy2:#071d3b;--teal:#3ecfa0;--teal-glow:#5ff5c0;
      --gold:#e8b84b;--cream:#e2ead6;--cream-dim:rgba(226,234,214,.55);
      --error:#f87171;--border:rgba(255,255,255,.08);
    }
    body{background:var(--bg);color:var(--cream);font-family:'Segoe UI',sans-serif;min-height:100vh}

    .topbar{background:var(--navy2);border-bottom:1px solid rgba(62,207,160,.12);padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
    .topbar-brand{font-size:1.2rem;font-weight:700;color:var(--teal-glow)}
    .topbar-right{display:flex;align-items:center;gap:1rem;font-size:.9rem}
    .notif-bell{position:relative;cursor:pointer;font-size:1.3rem}
    .notif-badge{position:absolute;top:-6px;right:-8px;background:#f87171;color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;display:flex;align-items:center;justify-content:center;font-weight:700}
    .logout-btn{padding:.4rem .9rem;background:rgba(248,113,113,.15);border:1px solid rgba(248,113,113,.3);border-radius:8px;color:#f87171;text-decoration:none;font-size:.82rem;transition:.2s}
    .logout-btn:hover{background:rgba(248,113,113,.25)}

    .main{max-width:1100px;margin:0 auto;padding:2rem}
    .welcome{margin-bottom:1.5rem}
    .welcome h1{font-size:1.6rem;font-weight:700;color:#fff}
    .welcome p{color:var(--cream-dim);font-size:.9rem;margin-top:.2rem}

    .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:2rem}
    .stat-card{background:var(--navy2);border:1px solid var(--border);border-radius:1rem;padding:1.2rem;text-align:center}
    .stat-num{font-size:2rem;font-weight:700;color:var(--teal-glow)}
    .stat-label{font-size:.78rem;color:var(--cream-dim);margin-top:.2rem}
    .stat-card.pending .stat-num{color:var(--gold)}
    .stat-card.accepted .stat-num{color:var(--teal-glow)}
    .stat-card.completed .stat-num{color:#7dd87d}
    .stat-card.missed-calls .stat-num{color:var(--error)}

    /* Page tabs */
    .page-tabs{display:flex;gap:.5rem;margin-bottom:1.5rem;border-bottom:1px solid var(--border);padding-bottom:.6rem}
    .ptab{padding:.5rem 1.2rem;border-radius:8px 8px 0 0;border:none;background:none;color:var(--cream-dim);cursor:pointer;font-size:.88rem;font-weight:500;transition:.2s;position:relative}
    .ptab.active{background:rgba(62,207,160,.12);color:var(--teal-glow);border:1px solid rgba(62,207,160,.25);border-bottom:none}
    .ptab:hover:not(.active){color:var(--cream)}
    .ptab .tab-badge{display:inline-block;background:#f87171;color:#fff;border-radius:10px;padding:.05rem .45rem;font-size:.65rem;margin-left:.3rem;font-weight:700;vertical-align:middle}

    .tab-content{display:none}
    .tab-content.active{display:block}

    .filter-tabs{display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap}
    .ftab{padding:.45rem 1rem;border-radius:20px;border:1px solid rgba(255,255,255,.1);background:none;color:var(--cream-dim);cursor:pointer;font-size:.82rem;transition:.2s}
    .ftab.active,.ftab:hover{background:var(--teal);border-color:var(--teal);color:#fff}

    .appt-list{display:flex;flex-direction:column;gap:1rem}
    .appt-card{background:var(--navy2);border:1px solid var(--border);border-radius:1rem;padding:1.3rem;transition:.2s;cursor:pointer}
    .appt-card:hover{border-color:rgba(62,207,160,.3);box-shadow:0 4px 20px rgba(0,0,0,.3)}
    .appt-card.new-notif{border-color:rgba(232,184,75,.4);background:rgba(232,184,75,.04)}
    .appt-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem}
    .appt-patient{font-size:1rem;font-weight:600;color:#fff}
    .appt-meta{font-size:.8rem;color:var(--cream-dim);margin-top:.2rem}
    .badge{display:inline-block;padding:.25rem .7rem;border-radius:20px;font-size:.72rem;font-weight:600}
    .badge-pending{background:rgba(232,184,75,.15);color:var(--gold)}
    .badge-accepted{background:rgba(62,207,160,.15);color:var(--teal-glow)}
    .badge-rejected{background:rgba(248,113,113,.15);color:var(--error)}
    .badge-completed{background:rgba(100,200,100,.15);color:#7dd87d}
    .badge-ringing{background:rgba(62,207,160,.15);color:var(--teal-glow)}
    .badge-answered{background:rgba(100,200,100,.15);color:#7dd87d}
    .badge-missed,.badge-declined{background:rgba(248,113,113,.15);color:var(--error)}
    .appt-body{margin-top:1rem;display:none}
    .appt-body.open{display:block}
    .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem .8rem;margin-bottom:1rem}
    .info-item{font-size:.82rem}
    .info-item .info-label{color:var(--cream-dim);font-size:.73rem;text-transform:uppercase;letter-spacing:.3px}
    .info-item .info-val{color:#e2ead6;margin-top:.15rem}
    .msg-box{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:.8rem;padding:.8rem 1rem;font-size:.85rem;color:#d0dcea;line-height:1.6;margin-bottom:1rem}
    .action-form{display:flex;flex-direction:column;gap:.7rem}
    .action-form textarea{padding:.7rem 1rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:.8rem;color:var(--cream);font-size:.85rem;resize:vertical;min-height:70px;font-family:inherit;outline:none}
    .action-form textarea:focus{border-color:var(--teal-glow)}
    .action-btns{display:flex;gap:.6rem;flex-wrap:wrap}
    .btn-accept{padding:.55rem 1.2rem;background:rgba(62,207,160,.15);border:1px solid rgba(62,207,160,.3);border-radius:.6rem;color:var(--teal-glow);cursor:pointer;font-weight:600;font-size:.85rem;transition:.2s}
    .btn-accept:hover{background:rgba(62,207,160,.25)}
    .btn-reject{padding:.55rem 1.2rem;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.25);border-radius:.6rem;color:var(--error);cursor:pointer;font-weight:600;font-size:.85rem;transition:.2s}
    .btn-reject:hover{background:rgba(248,113,113,.22)}
    .btn-complete{padding:.55rem 1.2rem;background:rgba(100,200,100,.12);border:1px solid rgba(100,200,100,.25);border-radius:.6rem;color:#7dd87d;cursor:pointer;font-weight:600;font-size:.85rem;transition:.2s}
    .btn-complete:hover{background:rgba(100,200,100,.22)}
    .status-note{font-size:.82rem;color:var(--cream-dim);padding:.5rem .8rem;background:rgba(255,255,255,.04);border-radius:.6rem;border-left:3px solid var(--teal)}
    .empty{text-align:center;padding:3rem;color:var(--cream-dim)}

    /* Call history table */
    .call-table{width:100%;border-collapse:collapse;font-size:.85rem}
    .call-table th{text-align:left;padding:.6rem .8rem;color:var(--cream-dim);font-size:.75rem;text-transform:uppercase;border-bottom:1px solid var(--border)}
    .call-table td{padding:.7rem .8rem;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
    .call-table tr:hover td{background:rgba(255,255,255,.025)}
    .call-join-btn{padding:.3rem .75rem;border-radius:6px;background:rgba(62,207,160,.12);border:1px solid rgba(62,207,160,.25);color:var(--teal-glow);text-decoration:none;font-size:.78rem;font-weight:600;transition:.2s;white-space:nowrap}
    .call-join-btn:hover{background:rgba(62,207,160,.22)}

    /* Incoming-call overlay (single, clean implementation) */
    #callOverlay{display:none;position:fixed;inset:0;z-index:9000;background:rgba(2,8,18,.9);backdrop-filter:blur(8px);align-items:center;justify-content:center;flex-direction:column;gap:1.2rem;text-align:center;padding:2rem}
    #callOverlay.show{display:flex}
    .call-ring-icon{font-size:4rem;animation:ringPulse 1s ease-in-out infinite}
    @keyframes ringPulse{0%,100%{transform:scale(1) rotate(-8deg)}50%{transform:scale(1.15) rotate(8deg)}}
    .call-ring-name{font-size:1.6rem;font-weight:700;color:#fff;margin:0}
    .call-ring-sub{font-size:.9rem;color:rgba(200,220,240,.6);margin:0}
    .call-ring-link{display:inline-block;margin-top:.3rem;font-size:.82rem;color:var(--teal-glow);word-break:break-all;text-decoration:none;padding:.3rem .7rem;border:1px solid rgba(62,207,160,.3);border-radius:.5rem;max-width:440px}
    .call-ring-link:hover{background:rgba(62,207,160,.1)}
    .call-action-row{display:flex;gap:1rem;margin-top:.4rem;flex-wrap:wrap;justify-content:center}
    .btn-call-answer{padding:.7rem 1.8rem;background:#16a34a;border:none;border-radius:.8rem;color:#fff;font-size:1rem;font-weight:700;cursor:pointer;transition:.2s}
    .btn-call-answer:hover{background:#15803d}
    .btn-call-decline{padding:.7rem 1.8rem;background:rgba(248,113,113,.2);border:1px solid rgba(248,113,113,.35);border-radius:.8rem;color:#f87171;font-size:1rem;font-weight:700;cursor:pointer;transition:.2s}
    .btn-call-decline:hover{background:rgba(248,113,113,.32)}

    #missedCallBanner{display:none;background:rgba(232,184,75,.12);border:1px solid rgba(232,184,75,.3);border-radius:.9rem;padding:.8rem 1.2rem;margin-bottom:1.2rem;font-size:.87rem;color:var(--gold);cursor:pointer}
    #missedCallBanner.show{display:block}

    @media(max-width:600px){.info-grid{grid-template-columns:1fr}.action-btns{flex-direction:column}.call-table{font-size:.75rem}}
  </style>
</head>
<body>

<!-- ── Incoming-call overlay ────────────────────────────────────────────────── -->
<div id="callOverlay">
  <div class="call-ring-icon">📞</div>
  <p class="call-ring-name"  id="callPatientName">Patient Name</p>
  <p class="call-ring-sub"   id="callPatientSub">is calling you right now</p>
  <a class="call-ring-link"  id="callRoomLink" href="#" target="_blank" rel="noopener">Loading room link…</a>
  <div class="call-action-row">
    <button class="btn-call-answer"  id="btnAnswer">📹 Answer</button>
    <button class="btn-call-decline" id="btnDecline">❌ Decline</button>
  </div>
</div>

<!-- ── Top bar ───────────────────────────────────────────────────────────────── -->
<div class="topbar">
  <div class="topbar-brand">🏥 FClinic — Doctor Portal</div>
  <div class="topbar-right">
    <div class="notif-bell" title="Unread appointment notifications">
      🔔 <?php if ($unreadCount > 0): ?><span class="notif-badge"><?= $unreadCount ?></span><?php endif; ?>
    </div>
    <span>👨‍⚕️ <?= htmlspecialchars($_SESSION['doctor_name']) ?></span>
    <a href="logout.php?role=doctor" class="logout-btn">Sign Out</a>
  </div>
</div>

<!-- ── Main ──────────────────────────────────────────────────────────────────── -->
<div class="main">

  <div class="welcome">
    <h1>Good day, Dr. <?= htmlspecialchars($_SESSION['doctor_name']) ?> 👋</h1>
    <p>Manage your appointments and video calls from here.</p>
  </div>

  <!-- Missed-call banner -->
  <div id="missedCallBanner" onclick="this.classList.remove('show')">
    📵 <span id="missedCallText"></span>
  </div>

  <!-- Stats row -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-num"><?= $total ?></div>
      <div class="stat-label">Total Appointments</div>
    </div>
    <div class="stat-card pending">
      <div class="stat-num"><?= $pending ?></div>
      <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card accepted">
      <div class="stat-num"><?= $accepted ?></div>
      <div class="stat-label">Accepted</div>
    </div>
    <div class="stat-card completed">
      <div class="stat-num"><?= $completed ?></div>
      <div class="stat-label">Completed</div>
    </div>
    <div class="stat-card missed-calls">
      <div class="stat-num"><?= $callMissed ?></div>
      <div class="stat-label">Missed Calls</div>
    </div>
  </div>

  <!-- Page tabs -->
  <div class="page-tabs">
    <button class="ptab active" onclick="switchTab('appointments', this)">
      📅 Appointments
    </button>
    <button class="ptab" onclick="switchTab('calls', this)">
      📞 Call History
      <?php if ($callMissed > 0): ?>
        <span class="tab-badge"><?= $callMissed ?></span>
      <?php endif; ?>
    </button>
  </div>

  <!-- ── TAB: Appointments ─────────────────────────────────────────────────── -->
  <div class="tab-content active" id="tab-appointments">
    <div class="filter-tabs">
      <button class="ftab active" onclick="filterAppts('all', this)">All</button>
      <button class="ftab" onclick="filterAppts('pending', this)">Pending</button>
      <button class="ftab" onclick="filterAppts('accepted', this)">Accepted</button>
      <button class="ftab" onclick="filterAppts('rejected', this)">Rejected</button>
      <button class="ftab" onclick="filterAppts('completed', this)">Completed</button>
    </div>

    <div class="appt-list" id="apptList">
      <?php if (!$appointments): ?>
        <div class="empty">📭 No appointments yet.</div>
      <?php else: ?>
        <?php foreach ($appointments as $a): ?>
          <div class="appt-card <?= (!$a['is_read']) ? 'new-notif' : '' ?>" data-status="<?= $a['status'] ?>">
            <div class="appt-header" onclick="toggleCard(this)">
              <div>
                <div class="appt-patient">
                  <?= (!$a['is_read']) ? '🔔 ' : '' ?>
                  <?= htmlspecialchars($a['patient_name']) ?>
                </div>
                <div class="appt-meta">
                  📂 <?= htmlspecialchars($a['division_name']) ?> &nbsp;|&nbsp;
                  📅 <?= htmlspecialchars($a['appt_date']) ?> at <?= htmlspecialchars($a['appt_time']) ?> &nbsp;|&nbsp;
                  ⏰ Submitted <?= date('M j, Y', strtotime($a['created_at'])) ?>
                </div>
              </div>
              <span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span>
            </div>

            <div class="appt-body">
              <div class="info-grid">
                <div class="info-item">
                  <div class="info-label">Patient Email</div>
                  <div class="info-val"><?= htmlspecialchars($a['patient_email']) ?></div>
                </div>
                <div class="info-item">
                  <div class="info-label">Phone</div>
                  <div class="info-val"><?= htmlspecialchars($a['patient_phone']) ?></div>
                </div>
                <div class="info-item">
                  <div class="info-label">City / Country</div>
                  <div class="info-val"><?= htmlspecialchars($a['patient_city'] ?: '—') ?></div>
                </div>
                <div class="info-item">
                  <div class="info-label">Date &amp; Time</div>
                  <div class="info-val"><?= htmlspecialchars($a['appt_date'] . ' @ ' . $a['appt_time']) ?></div>
                </div>
              </div>

              <?php if ($a['message']): ?>
                <div class="msg-box">💬 <strong>Patient Message:</strong><br><?= nl2br(htmlspecialchars($a['message'])) ?></div>
              <?php endif; ?>

              <?php if ($a['doctor_note']): ?>
                <div class="status-note">📝 <strong>Your Note:</strong> <?= htmlspecialchars($a['doctor_note']) ?></div>
              <?php endif; ?>

              <?php if ($a['status'] !== 'completed' && $a['status'] !== 'rejected'): ?>
              <form method="post" action="" class="action-form">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="appt_id"       value="<?= $a['id'] ?>">
                <input type="hidden" name="csrf_token"    value="<?= $csrf ?>">
                <input type="hidden" name="new_status"    id="status-<?= $a['id'] ?>" value="">
                <textarea name="doctor_note" placeholder="Add a note for the patient (optional)…"><?= htmlspecialchars($a['doctor_note'] ?? '') ?></textarea>
                <div class="action-btns">
                  <?php if ($a['status'] === 'pending'): ?>
                    <button type="submit" class="btn-accept"   onclick="setStatus('<?= $a['id'] ?>','accepted')">✅ Accept</button>
                    <button type="submit" class="btn-reject"   onclick="setStatus('<?= $a['id'] ?>','rejected')">❌ Reject</button>
                  <?php elseif ($a['status'] === 'accepted'): ?>
                    <button type="submit" class="btn-complete" onclick="setStatus('<?= $a['id'] ?>','completed')">✔ Mark Completed</button>
                    <button type="submit" class="btn-reject"   onclick="setStatus('<?= $a['id'] ?>','rejected')">❌ Reject</button>
                  <?php endif; ?>
                </div>
              </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div><!-- /tab-appointments -->

  <!-- ── TAB: Call History ──────────────────────────────────────────────────── -->
  <div class="tab-content" id="tab-calls">
    <?php if (!$callHistory): ?>
      <div class="empty">📵 No video call history yet.</div>
    <?php else: ?>
      <table class="call-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Patient</th>
            <th>Contact</th>
            <th>Status</th>
            <th>Date &amp; Time</th>
            <th>Room</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($callHistory as $c): ?>
            <tr>
              <td style="color:var(--cream-dim);font-size:.75rem"><?= $c['id'] ?></td>
              <td>
                <strong><?= htmlspecialchars($c['patient_name']) ?></strong>
              </td>
              <td style="font-size:.78rem;color:var(--cream-dim)">
                <?= htmlspecialchars($c['patient_email']) ?><br>
                <?= htmlspecialchars($c['patient_phone'] ?: '—') ?>
              </td>
              <td>
                <span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span>
              </td>
              <td style="font-size:.8rem;color:var(--cream-dim);white-space:nowrap">
                <?= date('M j, Y', strtotime($c['created_at'])) ?><br>
                <?= date('H:i', strtotime($c['created_at'])) ?>
              </td>
              <td>
                <a href="doctor_video_call.php?room=<?= urlencode($c['room_url']) ?>"
                   class="call-join-btn" target="_blank" rel="noopener">
                  📹 Join
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div><!-- /tab-calls -->

</div><!-- /main -->


<!-- ─────────────────────────────────────────────────────────────────────────────
     Incoming-call polling (single, definitive implementation)
     Polls call_poll.php every 1.5 seconds for new ringing calls.
     Shows overlay with patient name, contact info, and room link.
───────────────────────────────────────────────────────────────────────────── -->
<script>
(function () {
  'use strict';

  const overlay      = document.getElementById('callOverlay');
  const nameEl       = document.getElementById('callPatientName');
  const subEl        = document.getElementById('callPatientSub');
  const linkEl       = document.getElementById('callRoomLink');
  const btnAnswer    = document.getElementById('btnAnswer');
  const btnDecline   = document.getElementById('btnDecline');
  const missedBanner = document.getElementById('missedCallBanner');
  const missedText   = document.getElementById('missedCallText');

  let activeCallId  = null;
  let shownCallIds  = new Set();
  let ringInterval  = null;
  let titleInterval = null;
  let audioCtx      = null;
  const origTitle   = document.title;

  // ── Audio beep ──────────────────────────────────────────────────────────────
  function beep() {
    try {
      if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = audioCtx.createOscillator();
      const gn  = audioCtx.createGain();
      osc.connect(gn); gn.connect(audioCtx.destination);
      osc.frequency.value = 660;
      gn.gain.setValueAtTime(0.35, audioCtx.currentTime);
      gn.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.45);
      osc.start(audioCtx.currentTime);
      osc.stop(audioCtx.currentTime + 0.45);
    } catch(e) {}
  }

  function startRing() {
    beep();
    ringInterval  = setInterval(beep, 1000);
    let blink     = false;
    titleInterval = setInterval(() => {
      document.title = (blink = !blink) ? '📞 INCOMING CALL!' : origTitle;
    }, 700);
  }

  function stopRing() {
    clearInterval(ringInterval);  ringInterval  = null;
    clearInterval(titleInterval); titleInterval = null;
    document.title = origTitle;
  }

  // ── Show/hide overlay ───────────────────────────────────────────────────────
  function showCall(call) {
    activeCallId       = call.id;
    nameEl.textContent = call.patient_name;
    subEl.textContent  = '📧 ' + (call.patient_email || '—') +
                         '  |  📞 ' + (call.patient_phone || '—') +
                         ' — is calling you right now';
    linkEl.href        = call.room_url;
    linkEl.textContent = call.room_url;
    overlay.classList.add('show');
    startRing();
    sendBrowserNotif(call.patient_name);
  }

  function hideOverlay() {
    overlay.classList.remove('show');
    stopRing();
    activeCallId = null;
  }

  // ── Answer ──────────────────────────────────────────────────────────────────
  btnAnswer.onclick = function () {
    if (!activeCallId) return;
    const cid  = activeCallId;
    const room = linkEl.href;
    fetch('call_poll.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({action:'answer', call_id: cid})
    }).catch(() => {});
    window.open(
      'doctor_video_call.php?room=' + encodeURIComponent(room),
      '_blank'
    );
    hideOverlay();
  };

  // ── Decline ─────────────────────────────────────────────────────────────────
  btnDecline.onclick = function () {
    if (!activeCallId) return;
    const cid  = activeCallId;
    const name = nameEl.textContent;
    const room = linkEl.href;
    fetch('call_poll.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({action:'decline', call_id: cid})
    }).catch(() => {});
    hideOverlay();
    showMissedBanner('You declined the call from ' + name, room);
  };

  function showMissedBanner(msg, roomUrl) {
    missedText.innerHTML = '';
    missedText.textContent = msg + '. Room: ';
    const a = document.createElement('a');
    a.href = 'doctor_video_call.php?room=' + encodeURIComponent(roomUrl);
    a.target = '_blank'; a.rel = 'noopener';
    a.style.color = 'var(--gold)';
    a.textContent = roomUrl;
    missedText.appendChild(a);
    missedBanner.classList.add('show');
  }

  // ── Browser notification ────────────────────────────────────────────────────
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
  }
  function sendBrowserNotif(name) {
    if ('Notification' in window && Notification.permission === 'granted') {
      const n = new Notification('📞 Incoming Call', {
        body: name + ' is calling you. Switch to the tab to answer.',
        requireInteraction: true,
        tag: 'incoming-call'
      });
      n.onclick = () => { window.focus(); n.close(); };
    }
  }

  // ── Poll ────────────────────────────────────────────────────────────────────
  async function poll() {
    try {
      const res  = await fetch('call_poll.php');
      if (!res.ok) return;
      const data = await res.json();
      if (!data.ok) return;

      const ringing = data.calls || [];

      // Auto-dismiss overlay if patient cancelled
      if (activeCallId && !ringing.find(c => String(c.id) === String(activeCallId))) {
        const name = nameEl.textContent;
        const room = linkEl.href;
        hideOverlay();
        showMissedBanner('Missed call from ' + name + ' — they may have hung up', room);
        return;
      }

      // Show first unseen call
      for (const call of ringing) {
        if (!shownCallIds.has(call.id)) {
          shownCallIds.add(call.id);
          showCall(call);
          break;
        }
      }
    } catch(e) {}
  }

  poll();
  setInterval(poll, 1500);
})();
</script>

<!-- Appointment UI helpers -->
<script>
function toggleCard(headerEl) {
  const body = headerEl.closest('.appt-card').querySelector('.appt-body');
  body.classList.toggle('open');
}
function setStatus(id, status) {
  document.getElementById('status-' + id).value = status;
}
function filterAppts(status, btn) {
  document.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.appt-card').forEach(card => {
    card.style.display = (status === 'all' || card.dataset.status === status) ? '' : 'none';
  });
}
function switchTab(name, btn) {
  document.querySelectorAll('.ptab').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('tab-' + name).classList.add('active');
}
// Auto-expand unread appointment cards
document.querySelectorAll('.appt-card.new-notif').forEach(c => {
  c.querySelector('.appt-body').classList.add('open');
});
</script>
</body>
</html>
