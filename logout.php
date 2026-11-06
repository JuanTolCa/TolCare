<?php
// TolCare — log out: destroy the session and go back to the login page.
session_start();
$_SESSION = [];
session_destroy();
header('Location: /login.php');
exit;
