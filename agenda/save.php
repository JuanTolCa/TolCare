<?php
// TolCare — save the generated agenda into agenda_shifts.
// Re-generates the matrix with the same shared logic as the preview
// (shift_logic.php), so what is saved is exactly what was shown.
// Only draft agendas can be saved. Past dates are locked: they are not
// saved (and previously saved future shifts are kept). Re-saving is safe:
// this tab's future shifts are replaced, not duplicated.
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once 'shift_logic.php';

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
$shift_type = ($shift === 'Night') ? 'night' : 'day';

// The agenda must exist and be a draft.
$stmt = $conn->prepare("SELECT * FROM agendas WHERE id = ?");
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

$result = generate_agenda_shifts(
    $conn, $agenda_id, $team, $shift,
    $agenda['start_date'], $agenda['end_date']
);

$today = date('Y-m-d');
$saved = 0;
$skipped = 0;

// Replace this tab's saved FUTURE shifts (past ones are locked, keep them).
$stmt = $conn->prepare("DELETE FROM agenda_shifts
    WHERE agenda_id = ? AND team = ? AND shift_type = ? AND shift_date >= ?");
$stmt->bind_param("iiss", $agenda_id, $team, $shift_type, $today);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("INSERT INTO agenda_shifts
    (agenda_id, shift_date, shift_type, emp_id, team, position_id, start_time, end_time)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($result['shifts'] as $s) {
    if ($s['shift_date'] < $today) {
        $skipped++;
        continue;
    }
    $stmt->bind_param(
        "issiiiss",
        $s['agenda_id'], $s['shift_date'], $s['shift_type'], $s['emp_id'],
        $s['team'], $s['position_id'], $s['start_time'], $s['end_time']
    );
    $stmt->execute();
    $saved++;
}
$stmt->close();

header("Location: agenda_builder.php?id=$agenda_id&team=$team&shift=$shift&saved=$saved&skipped=$skipped");
exit;
