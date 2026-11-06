<?php
// Page title for the browser tab (e.g. "Edit Employee — TolCare").
$pageTitles = [
    'index.php' => 'Home',
    'under.php' => 'Under Construction',
    'list.php' => 'Employee Catalog',
    'add.php' => 'Add Employee',
    'edit.php' => 'Edit Employee',
    'view.php' => 'View Employee',
    'availability.php' => 'Employee Availability',
    'create.php' => 'Create Agenda',
    'agenda_builder.php' => 'Agenda Builder',
    'parameters.php' => 'Workforce Parameters',
    'schedule_settings.php' => 'Schedule Configuration',
];
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$browserTitle = isset($pageTitles[$currentPage])
    ? $pageTitles[$currentPage] . ' — TolCare'
    : 'TolCare';
// The username comes from the login session (auth.php runs before this file).
$username = $_SESSION['username'] ?? 'unknown';
$date = date("Y-m-d H:i:s");
$stmt = $conn->prepare("INSERT INTO logs (username, action) VALUES (?, 'Accessed system')");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo htmlspecialchars($browserTitle); ?></title>
<link rel="stylesheet" href="../css/style.css">
<script>
function confirmAction(msg){
    return confirm(msg);
}
</script>
</head>
<body>
<div class="header-top">
Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong> &nbsp;|&nbsp; Date: <?php echo date("Y-m-d"); ?> &nbsp;|&nbsp; <a href="/logout.php">Log out</a>
</div>
