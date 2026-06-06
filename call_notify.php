<?php
/**
 * call_notify.php — Patient initiates a video call.
 * POST JSON: { doctor_id: int, room_url: string }
 * Returns:   { ok: true, call_id: int }
 */

require_once 'db.php';
require_patient();   // redirects to index.php if not logged in

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$pdo        = get_pdo();
$patient_id = (int) $_SESSION['patient_id'];

$body      = json_decode(file_get_contents('php://input'), true) ?? [];
$doctor_id = (int) ($body['doctor_id'] ?? 0);
$room_url  = trim($body['room_url'] ?? '');

if (!$doctor_id || !$room_url) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing doctor_id or room_url']);
    exit;
}

// Validate room URL format (Jitsi only)
if (!preg_match('#^https://meet\.jit\.si/[a-zA-Z0-9\-_]{3,100}$#', $room_url)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid room URL format']);
    exit;
}

// Verify that the doctor actually exists
$doctorCheck = $pdo->prepare('SELECT id FROM doctors WHERE id = ?');
$doctorCheck->execute([$doctor_id]);
if (!$doctorCheck->fetch()) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Doctor not found']);
    exit;
}

// Mark any previous ringing calls from this patient to this doctor as missed
$pdo->prepare(
    "UPDATE call_requests SET status='missed'
     WHERE patient_id=? AND doctor_id=? AND status='ringing'"
)->execute([$patient_id, $doctor_id]);

// Insert the new call request
$pdo->prepare(
    "INSERT INTO call_requests (patient_id, doctor_id, room_url, status)
     VALUES (?, ?, ?, 'ringing')"
)->execute([$patient_id, $doctor_id, $room_url]);

$call_id = (int) $pdo->lastInsertId();

echo json_encode(['ok' => true, 'call_id' => $call_id]);
