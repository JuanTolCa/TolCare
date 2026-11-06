<?php require_once '../includes/auth.php'; ?>
<?php include '../config/db.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<div class="content">

<?php
$id = intval($_GET['id']);
$message = "";
$messageType = "";

$emp_id = $id;
$stmt = $conn->prepare("
SELECT e.*, p.position_name
FROM employees e
LEFT JOIN positions p ON e.position_id = p.id
WHERE e.emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ================= DELETE ================= */
if(isset($_GET['delete'])){
    $del = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM availability WHERE id = ?");
    $stmt->bind_param("i", $del);
    $stmt->execute();
    $stmt->close();
    $message = "Availability record deleted successfully.";
    $messageType = "success";
}

/* ================= SAVE + VALIDATION ================= */
if(isset($_POST['save'])){

$type=$_POST['type'];

$single=$_POST['single_date'] ?? null;
$start=$_POST['start_date'] ?? null;
$end=$_POST['end_date'] ?? null;
$spec=$_POST['specific_day'] ?? null;
$stime=$_POST['start_time'] ?? null;
$etime=$_POST['end_time'] ?? null;

$overlap=false;

if($type=="SingleDate"){
    $stmt = $conn->prepare("SELECT id FROM availability
    WHERE emp_id = ?
    AND single_date = ?");
    $stmt->bind_param("is", $id, $single);
    $stmt->execute();
    $check = $stmt->get_result();
    if($check->num_rows>0) $overlap=true;
    $stmt->close();
}

if($type=="DateRange"){
    $stmt = $conn->prepare("SELECT id FROM availability
    WHERE emp_id = ?
    AND (start_date <= ? AND end_date >= ?)");
    $stmt->bind_param("iss", $id, $end, $start);
    $stmt->execute();
    $check = $stmt->get_result();
    if($check->num_rows>0) $overlap=true;
    $stmt->close();
}

if($type=="SpecificHours"){
    $stmt = $conn->prepare("SELECT id FROM availability
    WHERE emp_id = ?
    AND specific_day = ?
    AND (start_time <= ? AND end_time >= ?)");
    $stmt->bind_param("isss", $id, $spec, $etime, $stime);
    $stmt->execute();
    $check = $stmt->get_result();
    if($check->num_rows>0) $overlap=true;
    $stmt->close();
}

if($overlap){
    $message = "Conflict detected. This availability overlaps with an existing record.";
    $messageType = "error";
}else{
    $stmt = $conn->prepare("INSERT INTO availability
    (emp_id,type,single_date,start_date,end_date,specific_day,start_time,end_time)
    VALUES
    (?, ?,
    NULLIF(?,''),
    NULLIF(?,''),
    NULLIF(?,''),
    NULLIF(?,''),
    NULLIF(?,''),
    NULLIF(?,'')
    )");
    $stmt->bind_param("isssssss", $id, $type, $single, $start, $end, $spec, $stime, $etime);
    $stmt->execute();
    $stmt->close();

    $message = "Availability saved successfully.";
    $messageType = "success";
}
}
?>

<h2 class="section-title">Employee Availability</h2>

<h3>
Working on:
<strong>
<?php echo $emp['emp_id']." - ".$emp['first_name']." ".$emp['last_name']; ?>
(<?php echo $emp['position_name']; ?>)
</strong>
</h3>

<?php if($message != ""): ?>
<div class="notification <?php echo $messageType; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>

<hr>

<form method="POST">
<input type="hidden" name="emp_id" value="<?php echo $id; ?>">

<select name="type" id="typeSelect" onchange="toggleFields()" required>
<option value="">-- Select Availability Type --</option>
<option value="SingleDate">Single Date</option>
<option value="DateRange">Date Range</option>
<option value="SpecificHours">Specific Hours</option>
</select>

<br><br>

<div id="singleDateField" style="display:none;">
Single Date<br>
<input type="date" name="single_date">
</div>

<div id="dateRangeField" style="display:none;">
Start Date<br>
<input type="date" name="start_date"><br><br>
End Date<br>
<input type="date" name="end_date">
</div>

<div id="specificHoursField" style="display:none;">
Specific Day<br>
<input type="date" name="specific_day"><br><br>
Start Time<br>
<input type="time" name="start_time"><br><br>
End Time<br>
<input type="time" name="end_time">
</div>

<br>
<button type="submit" name="save">Save Availability</button>
<a href="list.php"><button type="button">Cancel</button></a>
</form>

<script>
function toggleFields(){
    let type = document.getElementById("typeSelect").value;

    document.getElementById("singleDateField").style.display="none";
    document.getElementById("dateRangeField").style.display="none";
    document.getElementById("specificHoursField").style.display="none";

    if(type === "SingleDate") {
        document.getElementById("singleDateField").style.display="block";
    }
    if(type === "DateRange") {
        document.getElementById("dateRangeField").style.display="block";
    }
    if(type === "SpecificHours") {
        document.getElementById("specificHoursField").style.display="block";
    }
}
</script>

<hr>

<h3>Saved Availability</h3>

<table border="1" width="100%">
<tr>
<th>Availability Type</th>
<th>Details</th>
<th>Actions</th>
</tr>

<?php
$stmt = $conn->prepare("SELECT * FROM availability WHERE emp_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$list = $stmt->get_result();
while($row=$list->fetch_assoc()){

$details="";

if($row['type']=="SingleDate"){
$details=$row['single_date'];
}
if($row['type']=="DateRange"){
$details=$row['start_date']." to ".$row['end_date'];
}
if($row['type']=="SpecificHours"){
$details=$row['specific_day']." ".$row['start_time']." - ".$row['end_time'];
}

echo "<tr>
<td>{$row['type']}</td>
<td>$details</td>
<td>
<a href='availability.php?id=$id&delete={$row['id']}'
class='delete-link'>
Delete
</a>
</td>
</tr>";
}
?>
</table>

<hr>

<h3>Calendar View</h3>

<select id="monthFilter" onchange="changeMonth()">
<?php
for($i=0;$i<6;$i++){
    $month = date("m", strtotime("+$i month"));
    $year  = date("Y", strtotime("+$i month"));
    $selected = ($i==0) ? "selected" : "";
    echo "<option value='$year-$month' $selected>
    ".date("F Y", strtotime("$year-$month-01"))."
    </option>";
}
?>
</select>

<div id="calendar" style="margin-top:20px;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 500,
        headerToolbar: {
            left: '',
            center: 'title',
            right: ''
        },
        events: 'load_events.php?id=<?php echo $id; ?>'
    });

    calendar.render();

    window.changeMonth = function(){
        let value = document.getElementById("monthFilter").value;
        calendar.gotoDate(value + "-01");
    }

});
</script>

</div>

<?php include '../includes/footer.php'; ?>
