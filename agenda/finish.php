<?php
// TolCare — finish (publish) an agenda: Draft -> Published.
// A published agenda is locked: it can no longer be saved or finished again,
// but it can still be exported.
require_once '../includes/auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit;
}

$agenda_id = (int)($_POST['agenda_id'] ?? 0);
$team = (int)($_POST['team'] ?? 1);
$shift = $_POST['shift'] ?? 'Day';
if ($shift !== 'Day' && $shift !== 'Night') {
    $shift = 'Day';
}

$stmt = $conn->prepare("SELECT status FROM agendas WHERE id = ?");
$stmt->bind_param("i", $agenda_id);
$stmt->execute();
$agenda = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$agenda) {
    header('Location: create.php');
    exit;
}
if ($agenda['status'] !== 'draft') {
    header("Location: agenda_builder.php?id=$agenda_id&team=$team&shift=$shift&error=published");
    exit;
}

$stmt = $conn->prepare("UPDATE agendas SET status = 'published' WHERE id = ?");
$stmt->bind_param("i", $agenda_id);
$stmt->execute();
$stmt->close();

header("Location: agenda_builder.php?id=$agenda_id&team=$team&shift=$shift&finished=1");
exit;
