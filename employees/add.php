<?php require_once '../includes/auth.php'; ?>
<?php include '../config/db.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>

<div class="content">
<h2 class="section-title">Add Employee</h2>

<?php
$result = $conn->query("SELECT IFNULL(MAX(emp_id),0)+1 AS next_id FROM employees");
$row = $result->fetch_assoc();
$next_id = $row['next_id'] ?? 1;

$positions = $conn->query("SELECT * FROM positions ORDER BY position_name ASC");

$success = false;

if($_POST){

$attachment = "";

if(!empty($_FILES['attachment']['name'])){
    $attachment = time()."_".basename($_FILES['attachment']['name']);
    move_uploaded_file($_FILES['attachment']['tmp_name'],"../uploads/".$attachment);
}

$emp_id = (int)($_POST['emp_id'] ?? 0);
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$position_id = (int)($_POST['position_id'] ?? 0);
$shift = $_POST['shift'] ?? 'Day';
$team = $_POST['team'] ?? '1';
$type = $_POST['type'] ?? '';
$extra1 = $_POST['extra1'] ?? '';
$extra2 = $_POST['extra2'] ?? '';
$extra3 = $_POST['extra3'] ?? '';
$extra4 = $_POST['extra4'] ?? '';
$extra5 = $_POST['extra5'] ?? '';
$extra6 = $_POST['extra6'] ?? '';
$extra7 = $_POST['extra7'] ?? '';
$extra8 = $_POST['extra8'] ?? '';
$extra9 = $_POST['extra9'] ?? '';
$extra10 = $_POST['extra10'] ?? '';

$stmt = $conn->prepare("INSERT INTO employees (
emp_id,first_name,last_name,position_id,shift,team,type,status,
extra1,extra2,extra3,extra4,extra5,extra6,extra7,extra8,extra9,extra10,
attachment,created_at
) VALUES (?,?,?,?,?,?,?,'Active',?,?,?,?,?,?,?,?,?,?,?,NOW())");
$stmt->bind_param("ississssssssssssss",
    $emp_id, $first_name, $last_name, $position_id, $shift, $team, $type,
    $extra1, $extra2, $extra3, $extra4, $extra5,
    $extra6, $extra7, $extra8, $extra9, $extra10,
    $attachment);
$success = $stmt->execute();
$stmt->close();
}
?>

<form method="POST" enctype="multipart/form-data" id="employeeForm">

<div class="section-box">
<h3>Basic Information</h3>

ID
<input type="number" name="emp_id" value="<?php echo $next_id; ?>" readonly>

First Name
<input name="first_name" required>

Last Name
<input name="last_name" required>

Position
<select name="position_id" required>
<option value="">Select Position</option>
<?php while($pos = $positions->fetch_assoc()){ ?>
<option value="<?php echo $pos['id']; ?>">
<?php echo $pos['position_name']; ?>
</option>
<?php } ?>
</select>

Shift
<select name="shift">
<option value="Day">Day</option>
<option value="Night">Night</option>
</select>

Team
<select name="team">
<option value="1">1</option>
<option value="2">2</option>
</select>

Type
<input name="type">
</div>

<div class="section-box">
<h3>Additional Information</h3>

<?php for($i=1;$i<=10;$i++){ ?>
Extra<?php echo $i; ?>
<input name="extra<?php echo $i; ?>">
<?php } ?>

Attachment
<input type="file" name="attachment">
</div>

<button type="button" onclick="openConfirmModal()">Save Employee</button>
<a href="list.php"><button type="button">Cancel</button></a>

</form>
</div>

<!-- CONFIRM MODAL -->
<div id="confirmModal" class="modal">
<div class="modal-content">
<h3>Confirm Action</h3>
<p>Are you sure you want to save this employee?</p>
<button onclick="submitForm()">Yes, Save</button>
<button onclick="closeConfirmModal()">Cancel</button>
</div>
</div>

<!-- SUCCESS MODAL -->
<?php if($success){ ?>
<div id="successModal" class="modal show">
<div class="modal-content success">
<h3>Success</h3>
<p>Employee saved successfully.</p>
<button onclick="goToList()">Go to Employee List</button>
</div>
</div>
<?php } ?>

<script>
function openConfirmModal(){
document.getElementById("confirmModal").classList.add("show");
}

function closeConfirmModal(){
document.getElementById("confirmModal").classList.remove("show");
}

function submitForm(){
document.getElementById("employeeForm").submit();
}

function goToList(){
window.location="list.php";
}
</script>

<?php include '../includes/footer.php'; ?>
