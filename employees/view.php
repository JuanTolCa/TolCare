<?php require_once '../includes/auth.php'; ?>
<?php include '../config/db.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>

<div class="content">
<h2 class="section-title">View Employee</h2>

<?php
$id = intval($_GET['id']);

$stmt = $conn->prepare("
SELECT e.*, p.position_name
FROM employees e
LEFT JOIN positions p ON e.position_id = p.id
WHERE e.emp_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<style>
.readonly-input {
    background:#f2f2f2;
}
.tab-buttons button {
    margin-right:5px;
}
.tab-content {
    display:none;
    margin-top:15px;
}
.tab-content.active {
    display:block;
}
.section-box input {
    display:block;
    margin-bottom:10px;
}
</style>

<script>
function openTab(tabName){
    let tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(t => t.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
}
</script>

<div class="tab-buttons">
<button type="button" onclick="openTab('basic')">Basic Information</button>
<button type="button" onclick="openTab('additional')">Additional Information</button>
<button type="button" onclick="openTab('availability')">Availability</button>
</div>

<!-- BASIC TAB -->
<div id="basic" class="tab-content active section-box">

ID
<input class="readonly-input" value="<?php echo $data['emp_id']; ?>" readonly>

First Name
<input class="readonly-input" value="<?php echo $data['first_name']; ?>" readonly>

Last Name
<input class="readonly-input" value="<?php echo $data['last_name']; ?>" readonly>

Position
<input class="readonly-input" value="<?php echo $data['position_name']; ?>" readonly>

Shift
<input class="readonly-input" value="<?php echo $data['shift']; ?>" readonly>

Team
<input class="readonly-input" value="<?php echo $data['team']; ?>" readonly>

Type
<input class="readonly-input" value="<?php echo $data['type']; ?>" readonly>

Status
<input class="readonly-input" value="<?php echo $data['status']; ?>" readonly>

</div>

<!-- ADDITIONAL TAB -->
<div id="additional" class="tab-content section-box">

<?php for($i=1;$i<=10;$i++){ ?>
Extra<?php echo $i; ?>
<input class="readonly-input" 
value="<?php echo $data["extra$i"]; ?>" readonly>
<?php } ?>

Attachment
<?php if(!empty($data['attachment'])){ ?>
<br>
<a href="../uploads/<?php echo $data['attachment']; ?>" target="_blank">
View Attachment
</a>
<?php } else { ?>
<input class="readonly-input" value="No attachment" readonly>
<?php } ?>

</div>

<!-- AVAILABILITY TAB -->
<div id="availability" class="tab-content section-box">

<h3>Availability Records</h3>

<table border="1" width="100%">
<tr>
<th>Type</th>
<th>Details</th>
</tr>

<?php
$emp_id = (int)$data['emp_id'];
$stmt = $conn->prepare("SELECT * FROM availability WHERE emp_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$avail = $stmt->get_result();

if($avail->num_rows==0){
echo "<tr><td colspan='2'>No availability records found</td></tr>";
}else{

while($row=$avail->fetch_assoc()){

$details="";

if($row['type']=="SingleDate"){
$details=$row['single_date'];
}
elseif($row['type']=="DateRange"){
$details=$row['start_date']." to ".$row['end_date'];
}
elseif($row['type']=="SpecificHours"){
$details=$row['specific_day']." ".$row['start_time']." - ".$row['end_time'];
}else{
$details="Not defined";
}

echo "<tr>
<td>{$row['type']}</td>
<td>$details</td>
</tr>";
}
}
?>
</table>

</div>

<br>
<a href="list.php"><button type="button">Back to List</button></a>

</div>
<?php include '../includes/footer.php'; ?>