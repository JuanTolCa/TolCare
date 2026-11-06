<?php require_once '../includes/auth.php'; ?>
<?php include '../config/db.php'; ?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>
<?php require_once 'shift_logic.php'; ?>

<?php

$agenda_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$team  = isset($_GET['team']) ? intval($_GET['team']) : 1;
$shift = isset($_GET['shift']) ? $_GET['shift'] : 'Day';
if ($shift !== 'Day' && $shift !== 'Night') { $shift = 'Day'; }

$stmt = $conn->prepare("SELECT * FROM agendas WHERE id = ?");
$stmt->bind_param("i", $agenda_id);
$stmt->execute();
$result = $stmt->get_result();

if (mysqli_num_rows($result) == 0) {
    die("Agenda not found");
}

$agenda = mysqli_fetch_assoc($result);

$start_date = $agenda['start_date'];
$end_date   = $agenda['end_date'];
$is_draft = ($agenda['status'] === 'draft');

$start = new DateTime($start_date);
$end   = new DateTime($end_date);

$days = [];

while ($start <= $end) {
    $days[] = clone $start;
    $start->modify("+1 day");
}

/* obtener tabs (team + shift) */

$stmt = $conn->prepare("
SELECT DISTINCT team, shift
FROM staffing_parameters
WHERE team = ?
AND shift = ?
ORDER BY team, shift
");
$stmt->bind_param("is", $team, $shift);
$stmt->execute();
$tabs_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>

    <title>Agenda Builder</title>

    <link rel="stylesheet" href="../css/agenda_builder.css">
    <script src="../js/agenda_builder.js"></script>

</head>

<body>

    <div class="content-area">

        <h2 class="section-title">Agenda Builder</h2>

        <h3>
            Agenda #<?php echo $agenda_id ?><br>
            <?php echo $start_date ?> → <?php echo $end_date ?>
        </h3>

        <?php if (isset($_GET['saved'])): ?>
        <div class="notification success">
            Agenda saved: <?php echo (int)$_GET['saved']; ?> shifts saved<?php if ((int)($_GET['skipped'] ?? 0) > 0): ?>, <?php echo (int)$_GET['skipped']; ?> past shifts skipped (past dates are locked)<?php endif; ?>.
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['finished'])): ?>
        <div class="notification success">
            Agenda published. It can no longer be changed, but you can still export it.
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'published'): ?>
        <div class="notification error">
            This agenda is already published and cannot be changed.
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'empty'): ?>
        <div class="notification error">
            Nothing to export yet — save the agenda first.
        </div>
        <?php endif; ?>

        <?php if (!$is_draft): ?>
        <div class="notification info">
            This agenda is <?php echo htmlspecialchars($agenda['status']); ?> — it cannot be changed. You can still export it.
        </div>
        <?php endif; ?>

        <div class="agenda-buttons">

            <form method="POST" action="save.php" style="display:inline;">
                <input type="hidden" name="agenda_id" value="<?php echo $agenda_id; ?>">
                <input type="hidden" name="team" value="<?php echo (int)$team; ?>">
                <input type="hidden" name="shift" value="<?php echo htmlspecialchars($shift); ?>">
                <button type="submit" class="btn-save" <?php if (!$is_draft) echo 'disabled'; ?>>Save</button>
            </form>

            <form method="POST" action="finish.php" style="display:inline;" onsubmit="return confirm('Publish this agenda? It can no longer be changed after that.');">
                <input type="hidden" name="agenda_id" value="<?php echo $agenda_id; ?>">
                <input type="hidden" name="team" value="<?php echo (int)$team; ?>">
                <input type="hidden" name="shift" value="<?php echo htmlspecialchars($shift); ?>">
                <button type="submit" class="btn-finish" <?php if (!$is_draft) echo 'disabled'; ?>>Finish Agenda</button>
            </form>

            <a href="export.php?id=<?php echo $agenda_id; ?>&team=<?php echo (int)$team; ?>&shift=<?php echo htmlspecialchars($shift); ?>"><button type="button" class="btn-export">Export CSV</button></a>

            <button class="btn-delete">Delete</button>
            <button class="btn-cancel" onclick="window.location='create.php'">Cancel</button>

        </div>
        <hr>

        <div class="tabs">

            <?php

            $tab_index = 0;

            while ($t = mysqli_fetch_assoc($tabs_result)) {

                $id = "team" . $t['team'] . "_" . $t['shift'];
                $active = ($tab_index == 0) ? "active" : "";

                echo "
                <div class='tab $active'
                id='tab_$id'
                onclick=\"openTab('$id')\">
                Team {$t['team']} - {$t['shift']}
                </div>
                ";

                $tab_index++;
            }

            mysqli_data_seek($tabs_result, 0);

            ?>

        </div>

        <?php

        $tab_index = 0;

        while ($tab = mysqli_fetch_assoc($tabs_result)) {

            $team  = $tab['team'];
            $shift = $tab['shift'];

            $id = "team" . $team . "_" . $shift;
            $active = ($tab_index == 0) ? "active" : "";

            // One shared generation pass: the preview shows exactly what
            // Save will store (shift_logic.php).
            $generated = generate_agenda_shifts($conn, $agenda_id, (int)$team, $shift, $start_date, $end_date);
            $employees = $generated['employees'];
            $lookup = [];
            foreach ($generated['shifts'] as $s) {
                $lookup[$s['emp_id']][$s['shift_date']] = $s['display'];
            }

        ?>

            <div id="<?php echo $id ?>" class="tabcontent <?php echo $active ?>">

                <h4>
                    Team <?php echo $team ?> |
                    Shift <?php echo ucfirst($shift) ?> |
                    <?php echo $start_date ?> → <?php echo $end_date ?>
                </h4>


                <div class="matrix-container">

                    <table>

                        <tr>

                            <th>Employee</th>
                            <th>Position</th>

                            <?php
                            foreach ($days as $d) {
                                echo "<th>" . $d->format("D<br>m-d") . "</th>";
                            }
                            ?>

                        </tr>

                        <?php

                        foreach ($employees as $emp) {

                            echo "<tr>";

                            echo "<td class='employee'>" . $emp['first_name'] . " " . $emp['last_name'] . "</td>";
                            echo "<td>" . $emp['position_name'] . "</td>";

                            foreach ($days as $d) {

                                $current_date = $d->format("Y-m-d");

                                echo "<td>";

                                if (isset($lookup[$emp['emp_id']][$current_date])) {
                                    echo "<div class='shiftbox'>" . $lookup[$emp['emp_id']][$current_date] . "</div>";
                                } else {
                                    echo "<div class='off'>OFF</div>";
                                }

                                echo "</td>";
                            }

                            echo "</tr>";
                        }

                        ?>

                    </table>

                </div>

            </div>

        <?php

            $tab_index++;
        }

        ?>

    </div>
    <?php include '../includes/footer.php'; ?>
