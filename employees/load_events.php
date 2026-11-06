<?php require_once '../includes/auth.php'; ?>
<?php
include '../config/db.php';

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM availability WHERE emp_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while($row = $result->fetch_assoc()){

    if($row['type']=="SingleDate"){
        $events[] = [
            "title" => "Unavailable",
            "start" => $row['single_date'],
            "color" => "#e74c3c"
        ];
    }

    if($row['type']=="DateRange"){
        $events[] = [
            "title" => "Unavailable",
            "start" => $row['start_date'],
            "end"   => date('Y-m-d', strtotime($row['end_date'].' +1 day')),
            "color" => "#f39c12"
        ];
    }

    if($row['type']=="SpecificHours"){
        $events[] = [
            "title" => "Unavailable ".$row['start_time']."-".$row['end_time'],
            "start" => $row['specific_day']."T".$row['start_time'],
            "end"   => $row['specific_day']."T".$row['end_time'],
            "color" => "#9b59b6"
        ];
    }
}

echo json_encode($events);
