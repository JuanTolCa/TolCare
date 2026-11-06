<?php require_once '../includes/auth.php'; ?>
<?php include '../config/db.php'; ?> 
<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>

<?php
$message = "";
$type = "";

$startError = "";
$endError = "";

$team_selected  = $_POST['team']  ?? 'both';
$shift_selected = $_POST['shift'] ?? 'both';

/* =========================================================
   STEP 1: Find last closed agenda
========================================================= */

$lastClosed = null;

$sql = "SELECT * FROM agendas
        WHERE status = 'closed'
        ORDER BY end_date DESC
        LIMIT 1";

$result = $conn->query($sql);

if($result && $result->num_rows > 0){
    $lastClosed = $result->fetch_assoc();
}

/* =========================================================
   STEP 2: Handle form submit
========================================================= */

if(isset($_POST['agenda_builder'])){

    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];

    $team_selected  = $_POST['team'];
    $shift_selected = $_POST['shift'];

    if(empty($start_date) || empty($end_date)){
        $startError = "Both dates are required.";
    } else {

        $startDay = date('l', strtotime($start_date));
        $endDay   = date('l', strtotime($end_date));

        if($startDay !== 'Saturday'){
            $startError = "Start date must be a Saturday.";
        }

        if($endDay !== 'Friday'){
            $endError = "End date must be a Friday.";
        }

        if(empty($startError) && empty($endError)){

            if($start_date > $end_date){
                $endError = "End date must be greater than Start date.";
            }
            else{

                if($lastClosed){

                    $minStart = date('Y-m-d', strtotime($lastClosed['end_date'].' +1 day'));

                    if($start_date < $minStart){
                        $startError = "Start date cannot be earlier than ".$minStart;
                    }
                    else{
                        $stmt = $conn->prepare("INSERT INTO agendas (start_date,end_date,status)
                                      VALUES(?, ?, 'draft')");
                        $stmt->bind_param("ss", $start_date, $end_date);
                        $stmt->execute();
                        $stmt->close();

                        $newId = $conn->insert_id;
                        header("Location: agenda_builder.php?id=".$newId);
                        exit;
                    }

                } else {

                    $stmt = $conn->prepare("INSERT INTO agendas (start_date,end_date,status)
                                  VALUES(?, ?, 'draft')");
                    $stmt->bind_param("ss", $start_date, $end_date);
                    $stmt->execute();
                    $stmt->close();

                    $newId = $conn->insert_id;
                    header("Location: agenda_builder.php?id=".$newId);
                    exit;
                }
            }
        }
    }
}
?>

<div class="content">

<h2 class="section-title">Create Agenda</h2>

<hr>

<?php if($lastClosed): ?>
<div class="notification info">
<strong>Last Closed Agenda:</strong><br>
ID <?php echo $lastClosed['id']; ?> |
<?php echo $lastClosed['start_date']; ?> to
<?php echo $lastClosed['end_date']; ?>
</div>
<?php
$minStart = date('Y-m-d', strtotime($lastClosed['end_date'].' +1 day'));
?>
<?php else: ?>
<div class="notification info">
<strong>First Agenda</strong><br>
No previously closed agendas found.
</div>
<?php
$minStart = "";
?>
<?php endif; ?>

<hr>

<form method="POST" id="agendaForm">

<div style="display:flex; gap:30px; align-items:center;">

<div>
<label>Team:</label><br>
<select name="team">
<option value="both" <?php if($team_selected=='both') echo 'selected'; ?>>Both Teams</option>
<option value="1" <?php if($team_selected=='1') echo 'selected'; ?>>Team 1</option>
<option value="2" <?php if($team_selected=='2') echo 'selected'; ?>>Team 2</option>
</select>
</div>

<div>
<label>Shift:</label><br>
<select name="shift">
<option value="both" <?php if($shift_selected=='both') echo 'selected'; ?>>Both Shifts</option>
<option value="day" <?php if($shift_selected=='day') echo 'selected'; ?>>Day</option>
<option value="night" <?php if($shift_selected=='night') echo 'selected'; ?>>Night</option>
</select>
</div>

</div>

<br>

<label>Start Date (Saturday only):</label><br>
<input type="date"
       id="start_date"
       name="start_date"
       value="<?php echo $_POST['start_date'] ?? ''; ?>"
       <?php if($minStart!=""): ?>
       min="<?php echo $minStart; ?>"
       <?php endif; ?>
       required>

<?php if(!empty($startError)): ?>
<div class="notification error">
<?php echo $startError; ?>
</div>
<?php endif; ?>

<br>

<label>End Date (Suggested automatically):</label><br>
<input type="date"
       id="end_date"
       name="end_date"
       value="<?php echo $_POST['end_date'] ?? ''; ?>"
       required>

<?php if(!empty($endError)): ?>
<div class="notification error">
<?php echo $endError; ?>
</div>
<?php endif; ?>

<br><br>

<button type="submit" name="agenda_builder">Create</button>
<a href="/index.php">Cancel</a>

</form>

</div>

<script>

document.getElementById("start_date").addEventListener("change", function(){

    const startInput = document.getElementById("start_date");
    const endInput   = document.getElementById("end_date");

    if(!startInput.value) return;

    let startDate = new Date(startInput.value);

    // +27 days = 4 weeks - 1 day (Friday)
    startDate.setDate(startDate.getDate() + 28);

    let year  = startDate.getFullYear();
    let month = String(startDate.getMonth() + 1).padStart(2, '0');
    let day   = String(startDate.getDate()).padStart(2, '0');

    endInput.value = year + "-" + month + "-" + day;

});

</script>

<?php include '../includes/footer.php'; ?>