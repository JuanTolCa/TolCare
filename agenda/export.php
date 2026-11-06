<?php
// TolCare — export the SAVED agenda as a CSV file (opens in Excel).
// Exports the current tab (team + shift). If nothing has been saved yet,
// it sends the user back to the builder with a message.
require_once '../includes/auth.php';
require_once '../config/db.php';

$agenda_id = (int)($_GET['id'] ?? 0);
$team = (int)($_GET['team'] ?? 1);
$shift = $_GET['shift'] ?? 'Day';
if ($shift !== 'Day' && $shift !== 'Night') {
    $shift = 'Day';
}
$shift_type = ($shift === 'Night') ? 'night' : 'day';

$stmt = $conn->prepare("SELECT * FROM agendas WHERE id = ?");
$stmt->bind_param("i", $agenda_id);
$stmt->execute();
$agenda = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$agenda) {
    die("Agenda not found");
}

$stmt = $conn->prepare("
    SELECT s.shift_date, s.shift_type, s.team,
           e.first_name, e.last_name, p.position_name,
           s.start_time, s.end_time
    FROM agenda_shifts s
    JOIN employees e ON e.emp_id = s.emp_id
    JOIN positions p ON p.id = s.position_id
    WHERE s.agenda_id = ? AND s.team = ? AND s.shift_type = ?
    ORDER BY s.shift_date, e.last_name, e.first_name");
$stmt->bind_param("iis", $agenda_id, $team, $shift_type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: agenda_builder.php?id=$agenda_id&team=$team&shift=$shift&error=empty");
    exit;
}

$filename = "agenda-" . $agenda_id . "-team" . $team . "-" . $shift_type . "-" . $agenda['start_date'] . "-to-" . $agenda['end_date'] . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Shift', 'Team', 'Employee', 'Position', 'Start', 'End']);
while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['shift_date'],
        ucfirst($row['shift_type']),
        $row['team'],
        $row['first_name'] . ' ' . $row['last_name'],
        $row['position_name'],
        $row['start_time'],
        $row['end_time'],
    ]);
}
fclose($out);
exit;
