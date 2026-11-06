<?php require_once '../includes/auth.php'; ?>
<?php include '../config/db.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<div class="content">

<h2 class="section-title">Schedule Configuration</h2>

<?php
$message="";
$type="";

/* ================= SAVE SETTINGS ================= */
if(isset($_POST['save_settings'])){

    $start_date=$_POST['start_date'];
    $starting_team=$_POST['starting_team'];

    /* Only one configuration allowed */
    $conn->query("DELETE FROM schedule_settings");

    $stmt = $conn->prepare("INSERT INTO schedule_settings (start_date,starting_team)
    VALUES(?, ?)");
    $stmt->bind_param("ss", $start_date, $starting_team);
    $stmt->execute();
    $stmt->close();

    $message="Schedule settings saved successfully.";
    $type="success";
}

/* ================= SAVE ROTATION ================= */
if(isset($_POST['save_rotation'])){

    /* Check if rotation already exists */
    $checkRotation = $conn->query("SELECT id FROM team_rotation_pattern LIMIT 1");

    if($checkRotation->num_rows > 0){
        $message="Rotation pattern already exists and cannot be modified.";
        $type="error";
    }else{
        foreach($_POST['pattern'] as $team=>$weeks){
            foreach($weeks as $week=>$days){
                $m=$days['monday'] ?? 0;
                $t=$days['tuesday'] ?? 0;
                $w=$days['wednesday'] ?? 0;
                $th=$days['thursday'] ?? 0;
                $f=$days['friday'] ?? 0;
                $s=$days['saturday'] ?? 0;
                $su=$days['sunday'] ?? 0;

                $weekNum = (int)$week;
                $m = (int)$m; $t = (int)$t; $w = (int)$w; $th = (int)$th;
                $f = (int)$f; $s = (int)$s; $su = (int)$su;
                $stmt = $conn->prepare("INSERT INTO team_rotation_pattern
                (team,week_number,monday,tuesday,wednesday,thursday,friday,saturday,sunday)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("siiiiiiii", $team, $weekNum, $m, $t, $w, $th, $f, $s, $su);
                $stmt->execute();
                $stmt->close();
            }
        }

        $message="Rotation pattern saved successfully.";
        $type="success";
    }
}
?>

<?php if($message!=""): ?>
<div class="notification <?php echo $type; ?>">
<?php echo $message; ?>
</div>
<?php endif; ?>

<hr>

<h3>Rotation Pattern</h3>
<?php
// Load rotation to pre-check
$rotationData = [];
$res = $conn->query("SELECT * FROM team_rotation_pattern");
while($r = $res->fetch_assoc()){
    $rotationData[$r['team']][$r['week_number']] = $r;
}

$rotationExists = !empty($rotationData);
?>

<form method="POST">
<table class="parameter-table">
<tr>
<th>Team</th>
<th>Week</th>
<th>Sat</th>
<th>Sun</th>
<th>Mon</th>
<th>Tue</th>
<th>Wed</th>
<th>Thu</th>
<th>Fri</th>
</tr>

<?php
$teams=['1','2'];
$weeks=['1','2'];
$days=['saturday','sunday','monday','tuesday','wednesday','thursday','friday'];

foreach($teams as $team){
    foreach($weeks as $week){
        echo "<tr>
        <td>Team $team</td>
        <td>Week $week</td>";
        foreach($days as $day){

            $isChecked = ($rotationData[$team][$week][$day] ?? 0) ? "checked" : "";
            $isDisabled = $rotationExists ? "disabled" : "";

            echo "<td>
                <input type='checkbox'
                name='pattern[$team][$week][$day]'
                value='1'
                $isChecked
                $isDisabled>
            </td>";
        }

        echo "</tr>";
    }
}
?>
</table>

<br>
<?php if(!$rotationExists): ?>
<button type="submit" name="save_rotation">Save Rotation Pattern</button>
<?php else: ?>
<div class="notification error">Rotation Pattern Locked</div>
<?php endif; ?>
</form>

<hr>

<h3>Initial Configuration</h3>

<?php
// Calculate date limits
$today = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+3 months'));
?>

<form method="POST">

Start Date (Saturdays only):
<input type="date"
       id="start_date"
       name="start_date"
       value="<?php echo $settings['start_date'] ?? ''; ?>"
       min="<?php echo $today; ?>"
       max="<?php echo $maxDate; ?>"
       required>

<!-- Starting team fixed to Team 1 -->
<input type="hidden" name="starting_team" value="1">

<p><strong>Starting Team:</strong> Team 1 (Fixed)</p>

<br>
<button type="submit" name="save_settings">Save Settings</button>

</form>

<script>
document.getElementById('start_date').addEventListener('change', function() {

    const selectedDate = new Date(this.value);
    const day = selectedDate.getDay(); // 5 = Saturday

    if (day !== 5) {
        alert('Only Saturdays are allowed as Start Date.  '+day);
        this.value = '';
    }

});
</script>


<hr>

<h3>Preview (Next 30 Days)</h3>
<div id="calendar"></div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 600,
        events: 'load_preview.php'
    });
    calendar.render();
});
</script>

<?php include '../includes/footer.php'; ?>
