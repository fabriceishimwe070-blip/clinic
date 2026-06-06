<?php

header('Content-Type: application/json');
require_once 'db.php';

$division_id = filter_input(INPUT_GET, 'division_id', FILTER_VALIDATE_INT);
if (!$division_id) {
    echo json_encode([]);
    exit;
}

$pdo  = get_pdo();
$stmt = $pdo->prepare(
    'SELECT d.id, d.full_name, d.specialty, d.bio
     FROM doctors d
     JOIN doctor_divisions dd ON dd.doctor_id = d.id
     WHERE dd.division_id = ?
     ORDER BY d.full_name'
);
$stmt->execute([$division_id]);
echo json_encode($stmt->fetchAll());
