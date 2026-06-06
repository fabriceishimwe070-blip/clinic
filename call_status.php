<?php
/**
 * call_status.php — Patient polls the status of their outgoing call.
 *
 * GET  ?call_id=N  → { ok, status }
 * POST { call_id, action:'cancel' } → mark call as missed
 */

require_once 'db.php';
require_patient();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$pdo        = get_pdo();
$patient_id = (int) $_SESSION['patient_id'];

// ── POST: patient cancels / hangs up ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $call_id = (int) ($body['call_id'] ?? 0);
    $action  = $body['action'] ?? '';

    if ($call_id && $action === 'cancel') {
        $pdo->prepare(
            "UPDATE call_requests SET status='missed'
             WHERE id=? AND patient_id=? AND status='ringing'"
        )->execute([$call_id, $patient_id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

// ── GET: poll for status ──────────────────────────────────────────────────────
$call_id = (int) ($_GET['call_id'] ?? 0);
if (!$call_id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing call_id']);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT status FROM call_requests WHERE id = ? AND patient_id = ?'
);
$stmt->execute([$call_id, $patient_id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Call not found']);
    exit;
}

echo json_encode(['ok' => true, 'status' => $row['status']]);
