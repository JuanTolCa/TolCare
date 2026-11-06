<?php require_once '../includes/auth.php'; ?>
<?php
include '../config/db.php';

// Load schedule settings
$settings = $conn->query("SELECT * FROM schedule_settings LIMIT 1")->fetch_assoc();
if (!$settings) exit;

$start_date = $settings['start_date']; // Debe ser sábado

// Load rotation patterns
$patterns = $conn->query("SELECT * FROM team_rotation_pattern");
$rotation = [];

while ($row = $patterns->fetch_assoc()) {
    $rotation[$row['team']][$row['week_number']] = $row;
}

$events = [];

/* =====================================================
   STEP 1: Determine first rotation week (always Week 1)
===================================================== */

$currentWeek = 1; // Semana A
$currentBlockStart = $start_date; // El bloque inicia el sábado seleccionado

/* =====================================================
   STEP 2: Generate 30-day preview
===================================================== */

for ($i = 0; $i < 30; $i++) {

    $date = date('Y-m-d', strtotime("$start_date +$i days"));
    $dayName = strtolower(date('l', strtotime($date)));

    // Si NO es el primer día y es sábado → cambia semana
    if ($i > 0 && $dayName == 'saturday') {
        $currentWeek = ($currentWeek == 1) ? 2 : 1;
    }

    $team1Works = $rotation['1'][$currentWeek][$dayName] ?? 0;
    $team2Works = $rotation['2'][$currentWeek][$dayName] ?? 0;

    if ($team1Works) {
        $workingTeam = '1';
    } elseif ($team2Works) {
        $workingTeam = '2';
    } else {
        $workingTeam = '';
    }

    if ($workingTeam != '') {
        $events[] = [
            'title' => "Team $workingTeam",
            'start' => $date,
            'color' => ($workingTeam == '1') ? '#007bff' : '#28a745'
        ];
    }
}

echo json_encode($events);