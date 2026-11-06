<?php require_once '../includes/auth.php'; ?>
<?php include '../config/db.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>

<div class="content">
<h2 class="section-title">Employee Catalog</h2>

<form method="GET">
<input type="text" name="search" placeholder="Search employee">
<button type="submit"><?php echo "\u{1F50E}"; ?> Search</button>
<br><br>
<a href="add.php"><button type="button">➕ Add Employee</button></a>
</form>

<br>

<table border="1" width="100%">
<tr>
<th>ID</th>
<th>Name</th>
<th>Position</th>
<th>Shift</th>
<th>Team</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php
$search = $_GET['search'] ?? '';
$like = '%' . $search . '%';

$stmt = $conn->prepare("
SELECT e.*, p.position_name
FROM employees e
LEFT JOIN positions p ON e.position_id = p.id
WHERE
e.first_name LIKE ?
OR e.last_name LIKE ?
OR e.emp_id LIKE ?
OR p.position_name LIKE ?
ORDER BY e.emp_id ASC
");
$stmt->bind_param("ssss", $like, $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
echo "<tr>
<td>{$row['emp_id']}</td>
<td>{$row['first_name']} {$row['last_name']}</td>
<td>{$row['position_name']}</td>
<td>{$row['shift']}</td>
<td>{$row['team']}</td>
<td>{$row['status']}</td>
<td>
<a href='view.php?id={$row['emp_id']}'>👁 View</a> |
<a href='edit.php?id={$row['emp_id']}'>✏ Edit</a> |
<a href='availability.php?id={$row['emp_id']}'>📅 Availability</a>
</td>
</tr>";
}
?>
</table>
</div>
<?php include '../includes/footer.php'; ?>
