<?php
/**
 * book_appointment.php — Patient books an appointment (POST only).
 * Redirects back to home.php with status query param.
 */

require_once 'db.php';
require_patient();
csrf_verify();

$patient_id  = (int) $_SESSION['patient_id'];
$division_id = filter_input(INPUT_POST, 'division_id', FILTER_VALIDATE_INT);
$doctor_id   = filter_input(INPUT_POST, 'doctor_id',   FILTER_VALIDATE_INT);
$appt_date   = trim($_POST['appt_date'] ?? '');
$appt_time   = trim($_POST['appt_time'] ?? '');
$message     = trim($_POST['message']   ?? '');

$errors = [];
if (!$division_id) $errors[] = 'Please select a division.';
if (!$doctor_id)   $errors[] = 'Please select a doctor.';
if (!$appt_date || !strtotime($appt_date) || $appt_date < date('Y-m-d'))
    $errors[] = 'Please choose a valid future date.';
if (!$appt_time)   $errors[] = 'Please choose an appointment time.';

if ($errors) {
    $msg = urlencode(implode(' | ', $errors));
    header("Location: home.php?appt=err&msg={$msg}");
    exit;
}

$pdo = get_pdo();

// Verify doctor belongs to the chosen division
$chk = $pdo->prepare(
    'SELECT 1 FROM doctor_divisions WHERE doctor_id=? AND division_id=?'
);
$chk->execute([$doctor_id, $division_id]);
if (!$chk->fetch()) {
    header('Location: home.php?appt=err&msg=Invalid+doctor+or+division');
    exit;
}

// Insert appointment
$ins = $pdo->prepare(
    'INSERT INTO appointments (patient_id, doctor_id, division_id, appt_date, appt_time, message)
     VALUES (?,?,?,?,?,?)'
);
$ins->execute([$patient_id, $doctor_id, $division_id, $appt_date, $appt_time, $message]);
$appt_id = (int) $pdo->lastInsertId();

// Notify the doctor
$pdo->prepare(
    'INSERT INTO notifications (doctor_id, appt_id) VALUES (?,?)'
)->execute([$doctor_id, $appt_id]);

header('Location: home.php?appt=ok');
exit;
