<?php
/**
 * call_poll.php — Doctor polls for incoming calls / answers or declines.
 *
 * GET  → returns all ringing calls for this doctor (with patient name, email, phone)
 * POST → { action: 'answer'|'decline', call_id: int }
 */

require_once 'db.php';
require_doctor();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$pdo       = get_pdo();
$doctor_id = (int) $_SESSION['doctor_id'];

// ── POST: answer or decline ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $action  = $body['action'] ?? '';
    $call_id = (int) ($body['call_id'] ?? 0);

    if ($call_id && in_array($action, ['answer', 'decline'], true)) {
        $status = ($action === 'answer') ? 'answered' : 'declined';
        $pdo->prepare(
            "UPDATE call_requests
             SET status = ?
             WHERE id = ? AND doctor_id = ? AND status = 'ringing'"
        )->execute([$status, $call_id, $doctor_id]);
    }

    echo json_encode(['ok' => true]);
    exit;
}

// ── GET: fetch all currently ringing calls ───────────────────────────────────
$stmt = $pdo->prepare("
    SELECT
        cr.id,
        cr.room_url,
        cr.created_at,
        u.Fname   AS patient_name,
        u.email   AS patient_email,
        u.phon    AS patient_phone
    FROM call_requests cr
    JOIN users u ON cr.patient_id = u.id
    WHERE cr.doctor_id = ? AND cr.status = 'ringing'
    ORDER BY cr.created_at DESC
");
$stmt->execute([$doctor_id]);

echo json_encode([
    'ok'    => true,
    'calls' => $stmt->fetchAll(PDO::FETCH_ASSOC),
]);
