<?php
// TolCare — session guard.
// Include FIRST on every protected page, before any output and before db.php:
//   require_once '../includes/auth.php';   (from a subfolder)
//   require_once 'includes/auth.php';      (from the root)
// If nobody is logged in, the visitor is sent to the login page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
