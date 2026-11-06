<?php
// TolCare — shared agenda generation logic.
// Used by agenda_builder.php (preview) and save.php (persist), so the saved
// agenda is always exactly what the preview shows.
//
// Returns ['employees' => [...], 'shifts' => [...]].
// Each shift row:
//   agenda_id, shift_date, shift_type ('day'|'night'), emp_id, team,
//   position_id, start_time, end_time, display
// 'display' is the text shown in the matrix cell, e.g. "06:00 - 18:00".
//
// Cell priority (same as the preview always had):
//   1. SpecificHours availability for that day (custom hours)
//   2. Blocked by SingleDate / DateRange availability -> OFF (no shift)
//   3. Rotation pattern (2-week rotation from the agenda start):
//      working day -> Day 06:00-18:00 / Night 18:00-06:00, else OFF.

function generate_agenda_shifts($conn, $agenda_id, $team, $shift, $start_date, $end_date) {
    $team = (int)$team;

    // Days in the agenda (same list the builder renders).
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = [];
    while ($start <= $end) {
        $days[] = clone $start;
        $start->modify("+1 day");
    }

    // Staffing: required employees per position for this team + shift.
    $stmt = $conn->prepare("
        SELECT sp.position_id, p.position_name as position, sp.required_qty
        FROM staffing_parameters sp
        JOIN positions p ON p.id = sp.position_id
        WHERE sp.team = ? AND sp.shift = ?");
    $stmt->bind_param("is", $team, $shift);
    $stmt->execute();
    $staffing_result = $stmt->get_result();

    $employees = [];
    while ($s = $staffing_result->fetch_assoc()) {
        $pos_id = (int)$s['position_id'];
        $limit = (int)$s['required_qty'];

        $stmt_emp = $conn->prepare("
            SELECT * FROM employees
            WHERE team = ? AND shift = ? AND position_id = ?
            LIMIT ?");
        $stmt_emp->bind_param("isii", $team, $shift, $pos_id, $limit);
        $stmt_emp->execute();
        $res_emp = $stmt_emp->get_result();

        while ($emp = $res_emp->fetch_assoc()) {
            $emp['position_name'] = $s['position'];
            $employees[] = $emp;
        }
        $stmt_emp->close();
    }
    $stmt->close();

    // Rotation pattern for this team: $rot[week_number][day_name] = 0/1.
    $stmt = $conn->prepare("SELECT * FROM team_rotation_pattern WHERE team = ?");
    $stmt->bind_param("i", $team);
    $stmt->execute();
    $res_rot = $stmt->get_result();
    $rot = [];
    while ($r = $res_rot->fetch_assoc()) {
        $rot[$r['week_number']] = $r;
    }
    $stmt->close();

    $shift_type = ($shift === 'Night') ? 'night' : 'day';
    $shifts = [];

    foreach ($employees as $emp) {
        $emp_id = (int)$emp['emp_id'];

        // Availability: SingleDate blocks.
        $stmt_av = $conn->prepare("
            SELECT single_date FROM availability
            WHERE emp_id = ? AND type = 'SingleDate'
            AND single_date BETWEEN ? AND ?");
        $stmt_av->bind_param("iss", $emp_id, $start_date, $end_date);
        $stmt_av->execute();
        $res_av = $stmt_av->get_result();
        $not_available = [];
        while ($av = $res_av->fetch_assoc()) {
            $not_available[] = $av['single_date'];
        }
        $stmt_av->close();

        // Availability: DateRange blocks.
        $stmt_range = $conn->prepare("
            SELECT start_date, end_date FROM availability
            WHERE emp_id = ? AND type = 'DateRange'
            AND (start_date <= ? AND end_date >= ?)");
        $stmt_range->bind_param("iss", $emp_id, $end_date, $start_date);
        $stmt_range->execute();
        $res_range = $stmt_range->get_result();
        $date_ranges = [];
        while ($r = $res_range->fetch_assoc()) {
            $date_ranges[] = ['start' => $r['start_date'], 'end' => $r['end_date']];
        }
        $stmt_range->close();

        // Availability: SpecificHours (custom hours for one day).
        $stmt_hours = $conn->prepare("
            SELECT specific_day, start_time, end_time FROM availability
            WHERE emp_id = ? AND type = 'SpecificHours'
            AND specific_day BETWEEN ? AND ?");
        $stmt_hours->bind_param("iss", $emp_id, $start_date, $end_date);
        $stmt_hours->execute();
        $res_hours = $stmt_hours->get_result();
        $specific_hours = [];
        while ($h = $res_hours->fetch_assoc()) {
            $specific_hours[$h['specific_day']] = [
                'start' => $h['start_time'],
                'end' => $h['end_time'],
            ];
        }
        $stmt_hours->close();

        // One cell per day.
        $index = 0;
        foreach ($days as $d) {
            $current_date = $d->format("Y-m-d");

            // 1. SpecificHours has priority.
            if (isset($specific_hours[$current_date])) {
                $shifts[] = [
                    'agenda_id' => $agenda_id,
                    'shift_date' => $current_date,
                    'shift_type' => $shift_type,
                    'emp_id' => $emp_id,
                    'team' => $team,
                    'position_id' => (int)$emp['position_id'],
                    'start_time' => $specific_hours[$current_date]['start'],
                    'end_time' => $specific_hours[$current_date]['end'],
                    'display' => $specific_hours[$current_date]['start'] . ' - ' . $specific_hours[$current_date]['end'],
                ];
            } else {
                // 2. Blocked?
                $blocked = in_array($current_date, $not_available);
                if (!$blocked) {
                    foreach ($date_ranges as $range) {
                        if ($current_date >= $range['start'] && $current_date <= $range['end']) {
                            $blocked = true;
                            break;
                        }
                    }
                }

                if (!$blocked) {
                    // 3. Rotation: week changes every 7 days from the agenda start.
                    $week = floor($index / 7) % 2 + 1;
                    $dayname = strtolower($d->format("l"));
                    $work = isset($rot[$week][$dayname]) ? (int)$rot[$week][$dayname] : 0;

                    if ($work) {
                        if ($shift === 'Day') {
                            $start_time = '06:00:00';
                            $end_time = '18:00:00';
                            $display = '06:00 - 18:00';
                        } else {
                            $start_time = '18:00:00';
                            $end_time = '06:00:00';
                            $display = '18:00 - 06:00';
                        }
                        $shifts[] = [
                            'agenda_id' => $agenda_id,
                            'shift_date' => $current_date,
                            'shift_type' => $shift_type,
                            'emp_id' => $emp_id,
                            'team' => $team,
                            'position_id' => (int)$emp['position_id'],
                            'start_time' => $start_time,
                            'end_time' => $end_time,
                            'display' => $display,
                        ];
                    }
                }
            }

            $index++;
        }
    }

    return ['employees' => $employees, 'shifts' => $shifts];
}
