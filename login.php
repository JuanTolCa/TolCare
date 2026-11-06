<?php
// TolCare — login page.
// If the users table is empty, this page works in setup mode and lets you
// create the first (admin) account. After that it behaves as a normal login.
session_start();
require_once 'config/db.php';

$error = '';
$success = '';
$setupMode = false;
$missingTable = false;

// Check whether the users table exists and whether it has any user yet.
$check = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($check === false) {
    $missingTable = true;
} else {
    $row = $check->fetch_assoc();
    $setupMode = ((int)$row['total'] === 0);
}

/* ================= CREATE FIRST USER (setup mode) ================= */
if ($setupMode && isset($_POST['setup'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($username) < 3) {
        $error = 'Username must have at least 3 characters.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must have at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hash);
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            session_regenerate_id(true);
            header('Location: /index.php');
            exit;
        } else {
            $error = 'Could not create the account. Try another username.';
        }
        $stmt->close();
    }
}

/* ================= NORMAL LOGIN ================= */
if (!$setupMode && !$missingTable && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        session_regenerate_id(true);
        header('Location: /index.php');
        exit;
    } else {
        $error = 'Wrong username or password.';
    }
}

// Already logged in? Go straight to the home page.
if (!empty($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>TolCare — Login</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="content home-container">
    <div class="welcome-card">
        <div class="logo-container">
            <img src="img/logo.jfif" alt="TolCare Logo" class="logo">
        </div>
        <h1 class="welcome-title">Welcome to TolCare</h1>
        <p class="welcome-subtitle">Employee Management &amp; Scheduling System</p>

        <?php if ($missingTable): ?>
            <div class="notification error">
                The users table does not exist yet.<br>
                In phpMyAdmin, open the <strong>tolcare</strong> database, go to
                <strong>Import</strong>, and import the file
                <strong>sql/001_users_table.sql</strong>. Then reload this page.
            </div>
        <?php elseif ($setupMode): ?>
            <h2 class="login-title">Create Admin Account</h2>
            <p class="welcome-text">No users yet. Create your admin account below:</p>
            <?php if ($error): ?>
                <div class="notification error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="section-box">
                    <label>Username</label>
                    <input name="username" required>
                    <label>Password (8+ characters)</label>
                    <input type="password" name="password" required>
                    <label>Confirm password</label>
                    <input type="password" name="confirm" required>
                </div>
                <br>
                <button type="submit" name="setup">Create Account</button>
            </form>
        <?php else: ?>
            <h2 class="login-title">Log In</h2>
            <p class="welcome-text">Please log in to continue.</p>
            <?php if ($error): ?>
                <div class="notification error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="section-box">
                    <label>Username</label>
                    <input name="username" required>
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <br>
                <button type="submit" name="login">Log In</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
