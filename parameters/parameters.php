<?php require_once '../includes/auth.php'; ?>
<?php include '../config/db.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>

<div class="content">

<h2 class="section-title">Workforce Parameters</h2>

<?php
$message="";
$type="";

/* ================= DELETE CONFIGURATION ================= */
if(isset($_GET['delete_config'])){
$team=$_GET['team'];
$shift=$_GET['shift'];

$stmt = $conn->prepare("DELETE FROM staffing_parameters WHERE team = ? AND shift = ?");
$stmt->bind_param("ss", $team, $shift);
$stmt->execute();
$stmt->close();

$message="Configuration deleted successfully.";
$type="success";
}

/* ================= SAVE / UPDATE SMART ================= */
if(isset($_POST['save'])){

$team=$_POST['team'];
$shift=$_POST['shift'];
$positions=$_POST['position'];

foreach($positions as $pos_id => $qty){
$pos_id = (int)$pos_id;
$qty = (int)$qty;

// Check if record exists
$stmt = $conn->prepare("
SELECT id FROM staffing_parameters
WHERE team = ?
AND shift = ?
AND position_id = ?");
$stmt->bind_param("ssi", $team, $shift, $pos_id);
$stmt->execute();
$check = $stmt->get_result();

if($check->num_rows>0){

if($qty > 0){
// UPDATE existing
$stmt = $conn->prepare("
UPDATE staffing_parameters
SET required_qty = ?
WHERE team = ?
AND shift = ?
AND position_id = ?");
$stmt->bind_param("issi", $qty, $team, $shift, $pos_id);
$stmt->execute();
$stmt->close();
}else{
// DELETE if qty = 0
$stmt = $conn->prepare("
DELETE FROM staffing_parameters
WHERE team = ?
AND shift = ?
AND position_id = ?");
$stmt->bind_param("ssi", $team, $shift, $pos_id);
$stmt->execute();
$stmt->close();
}

}else{

if($qty > 0){
// INSERT new
$stmt = $conn->prepare("
INSERT INTO staffing_parameters
(team,shift,position_id,required_qty)
VALUES(?, ?, ?, ?)");
$stmt->bind_param("ssii", $team, $shift, $pos_id, $qty);
$stmt->execute();
$stmt->close();
}

}

}

$message="Configuration saved successfully.";
$type="success";
}

?>

<?php if($message!=""): ?>
<div class="notification <?php echo $type; ?>">
<?php echo $message; ?>
</div>
<?php endif; ?>

<form method="POST">

<div class="parameter-top">

<select name="team" required>
<option value="">Select Team</option>
<option value="1">Team 1</option>
<option value="2">Team 2</option>
</select>

<select name="shift" required>
<option value="">Select Shift</option>
<option value="Day">Day</option>
<option value="Night">Night</option>
</select>

</div>

<hr>

<table class="parameter-table">
<tr>
<th>Position</th>
<th>Required Employees</th>
</tr>

<?php
$positions=$conn->query("SELECT * FROM positions ORDER BY position_name ASC");
while($p=$positions->fetch_assoc()){
echo "<tr>
<td>{$p['position_name']}</td>
<td><input type='number' min='0' name='position[{$p['id']}]' value='0'></td>
</tr>";
}
?>
</table>

<br>
<button type="submit" name="save">Save Parameters</button>

</form>

<hr>

<h3>Saved Configurations</h3>

<table class="parameter-table">
<tr>
<th>Team</th>
<th>Shift</th>
<th>Positions & Requirements</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php
$list=$conn->query("
SELECT team, shift
FROM staffing_parameters
GROUP BY team, shift
ORDER BY id DESC
");

while($row=$list->fetch_assoc()){

$team=$row['team'];
$shift=$row['shift'];

$stmt = $conn->prepare("
SELECT sp.*, p.position_name
FROM staffing_parameters sp
JOIN positions p ON sp.position_id=p.id
WHERE sp.team = ? AND sp.shift = ?");
$stmt->bind_param("ss", $team, $shift);
$stmt->execute();
$positionsData = $stmt->get_result();

$details="";
$insufficient=false;

while($p=$positionsData->fetch_assoc()){

$countPosId = (int)$p['position_id'];
$stmt = $conn->prepare("
SELECT COUNT(*) as total
FROM employees
WHERE position_id = ?
AND team = ?
AND shift = ?");
$stmt->bind_param("iss", $countPosId, $team, $shift);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

if($count < $p['required_qty']){
$insufficient=true;
}

$details .= $p['position_name']." (".$p['required_qty'].") | ";
}

$status = $insufficient 
? "<span class='badge red'>Insufficient</span>"
: "<span class='badge green'>OK</span>";

echo "<tr>
<td>Team {$team}</td>
<td>{$shift}</td>
<td>$details</td>
<td>$status</td>
<td>
<button onclick=\"openModal('$team','$shift')\">View</button>
<button class='btn-delete' 
onclick=\"confirmDelete('$team','$shift')\">
Delete
</button>
</td>
</tr>";
}
?>

</table>

</div>

<!-- VIEW MODAL -->
<div id="employeeModal" class="modal">
<div class="modal-content">
<h3>Employees Matching Configuration</h3>
<div id="modalConfig" class="modal-config"></div>
<hr>
<div id="modalBody"></div>
<br>
<div class="modal-actions">
<button class="btn-close" onclick="closeModal()">Close</button>
</div>
</div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div id="deleteModal" class="modal">
<div class="modal-content">
<h3>Delete Configuration</h3>
<p>Are you sure you want to delete this configuration?</p>
<br>
<div class="modal-actions">
<button id="confirmDeleteBtn" class="btn-delete">Yes, Delete</button>
<button class="btn-close" onclick="closeDelete()">Cancel</button>
</div>
</div>
</div>

<script>
function openModal(team,shift){

document.getElementById("modalConfig").innerHTML =
"<strong>Team " + team + " - " + shift + " Shift</strong>";

fetch("load_matching.php?team="+team+"&shift="+shift)
.then(response=>response.text())
.then(data=>{
document.getElementById("modalBody").innerHTML=data;
document.getElementById("employeeModal").style.display="flex";
});
}

function closeModal(){
document.getElementById("employeeModal").style.display="none";
}

function confirmDelete(team,shift){
document.getElementById("deleteModal").style.display="flex";

document.getElementById("confirmDeleteBtn").onclick=function(){
window.location="parameters.php?delete_config=1&team="+team+"&shift="+shift;
}
}

function closeDelete(){
document.getElementById("deleteModal").style.display="none";
}
</script>

<?php include '../includes/footer.php'; ?>
