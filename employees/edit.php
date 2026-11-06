<?php require_once '../includes/auth.php'; ?>
<?php include '../config/db.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>

<div class="content">
<h2 class="section-title">Edit Employee</h2>

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

$positions = $conn->query("SELECT * FROM positions");

if($_POST){

$attachment = $data['attachment'];

if(!empty($_FILES['attachment']['name'])){
    $attachment = time()."_".basename($_FILES['attachment']['name']);
    move_uploaded_file($_FILES['attachment']['tmp_name'],"../uploads/".$attachment);
}

$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$position_id = (int)($_POST['position_id'] ?? 0);
$shift = $_POST['shift'] ?? 'Day';
$team = $_POST['team'] ?? '1';
$type = $_POST['type'] ?? '';
$status = $_POST['status'] ?? 'Active';
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

$stmt = $conn->prepare("UPDATE employees SET
first_name=?, last_name=?, position_id=?, shift=?, team=?, type=?, status=?,
extra1=?, extra2=?, extra3=?, extra4=?, extra5=?,
extra6=?, extra7=?, extra8=?, extra9=?, extra10=?,
attachment=? WHERE emp_id=?");
$stmt->bind_param("ssisssssssssssssssi",
    $first_name, $last_name, $position_id, $shift, $team, $type, $status,
    $extra1, $extra2, $extra3, $extra4, $extra5,
    $extra6, $extra7, $extra8, $extra9, $extra10,
    $attachment, $id);
$stmt->execute();
$stmt->close();

echo "<script>alert('Employee updated successfully'); window.location='list.php';</script>";
}
?>

<style>
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
</style>

<script>
function openTab(tabName){
    let tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(t => t.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
}
</script>

<form method="POST" enctype="multipart/form-data"
onsubmit="return confirmAction('Are you sure you want to update this employee?')">

ID
<input value="<?php echo $data['emp_id']; ?>" disabled>

<div class="tab-buttons">
<button type="button" onclick="openTab('basic')">Basic Information</button>
<button type="button" onclick="openTab('additional')">Additional Information</button>
</div>

<!-- ================= BASIC TAB ================= -->
<div id="basic" class="tab-content active section-box">

First Name
<input name="first_name" value="<?php echo $data['first_name']; ?>" required>

Last Name
<input name="last_name" value="<?php echo $data['last_name']; ?>" required>

Position
<select name="position_id" required>
<?php while($pos = $positions->fetch_assoc()){ ?>
<option value="<?php echo $pos['id']; ?>"
<?php if($pos['id']==$data['position_id']) echo 'selected'; ?>>
<?php echo $pos['position_name']; ?>
</option>
<?php } ?>
</select>

Shift
<select name="shift">
<option value="Day" <?php if($data['shift']=='Day') echo 'selected'; ?>>Day</option>
<option value="Night" <?php if($data['shift']=='Night') echo 'selected'; ?>>Night</option>
</select>

Team
<select name="team">
<option value="1" <?php if($data['team']=='1') echo 'selected'; ?>>1</option>
<option value="2" <?php if($data['team']=='2') echo 'selected'; ?>>2</option>
</select>

Type
<input name="type" value="<?php echo $data['type']; ?>">

Status
<select name="status">
<option value="Active" <?php if($data['status']=='Active') echo 'selected'; ?>>Active</option>
<option value="Inactive" <?php if($data['status']=='Inactive') echo 'selected'; ?>>Inactive</option>
</select>

</div>

<!-- ================= ADDITIONAL TAB ================= -->
<div id="additional" class="tab-content section-box">

<?php for($i=1;$i<=10;$i++){ ?>
Extra<?php echo $i; ?>
<input name="extra<?php echo $i; ?>" 
value="<?php echo $data["extra$i"]; ?>">
<?php } ?>

Attachment
<input type="file" name="attachment">

<?php if(!empty($data['attachment'])){ ?>
<br>
Current File:
<a href="../uploads/<?php echo $data['attachment']; ?>" target="_blank">
View Attachment
</a>
<?php } ?>

</div>

<br>
<button type="submit">Update Employee</button>
<a href="list.php"><button type="button">Cancel</button></a>

</form>
</div>
<?php include '../includes/footer.php'; ?>
