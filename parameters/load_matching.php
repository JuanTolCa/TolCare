<?php require_once '../includes/auth.php'; ?>
<?php
include '../config/db.php';

$team=$_GET['team'];
$shift=$_GET['shift'];

$stmt = $conn->prepare("
SELECT e.emp_id, e.first_name, e.last_name, p.position_name
FROM employees e
JOIN positions p ON e.position_id=p.id
WHERE e.team = ?
AND e.shift = ?
ORDER BY p.position_name ASC
");
$stmt->bind_param("ss", $team, $shift);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows==0){
echo "<div class='notification error'>No employees found for this configuration.</div>";
}else{

echo "<table class='parameter-table'>
<tr>
<th>ID</th>
<th>Name</th>
<th>Position</th>
</tr>";

while($row=$result->fetch_assoc()){
echo "<tr>
<td>{$row['emp_id']}</td>
<td>{$row['first_name']} {$row['last_name']}</td>
<td>{$row['position_name']}</td>
</tr>";
}

echo "</table>";
}
?>

