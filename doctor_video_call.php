<?php
/**
 * doctor_video_call.php — Embeds the Jitsi room for the doctor.
 * ?room=https://meet.jit.si/ROOM_NAME
 */

require_once 'db.php';
require_doctor();

$room_url = trim($_GET['room'] ?? '');

// Validate the room URL to prevent open redirect / XSS
if (!preg_match('#^https://meet\.jit\.si/[a-zA-Z0-9\-_]{3,100}$#', $room_url)) {
    http_response_code(400);
    die('Invalid or missing room URL. <a href="doctor_dashboard.php">← Back to dashboard</a>');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Video Call — Doctor Portal</title>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { width: 100%; height: 100%; overflow: hidden; background: #04122a; }
    .topbar {
      position: fixed; top: 0; left: 0; right: 0; height: 44px;
      background: #071d3b; border-bottom: 1px solid rgba(62,207,160,.2);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 1.2rem; z-index: 10;
    }
    .topbar-brand { font-size: .95rem; font-weight: 700; color: #3ecfa0; font-family: sans-serif; }
    .topbar-back {
      padding: .3rem .8rem; border-radius: 6px; background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.1); color: #e2ead6; text-decoration: none;
      font-size: .8rem; font-family: sans-serif;
    }
    .topbar-back:hover { background: rgba(255,255,255,.12); }
    iframe {
      position: fixed; top: 44px; left: 0; right: 0; bottom: 0;
      width: 100%; height: calc(100% - 44px); border: none;
    }
  </style>
</head>
<body>
  <div class="topbar">
    <span class="topbar-brand">🏥 FClinic — Video Call</span>
    <a href="doctor_dashboard.php" class="topbar-back">← Dashboard</a>
  </div>
  <iframe
    src="<?= htmlspecialchars($room_url, ENT_QUOTES, 'UTF-8') ?>"
    allow="camera; microphone; fullscreen; display-capture; autoplay"
    allowfullscreen
  ></iframe>
</body>
</html>
