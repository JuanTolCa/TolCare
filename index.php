<?php require_once 'includes/auth.php'; ?>
<?php include 'config/db.php'; ?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/menu.php'; ?>

<div class="content home-container">

    <div class="welcome-card">
        <div class="logo-container">
            <?php
            $pathimage = "img/logo.jfif";
            echo "<img src='$pathimage' alt='TolCare Logo' class='logo'>";
            ?>
        </div>

        <h1 class="welcome-title">Welcome to TolCare</h1>

        <p class="welcome-subtitle">
            Employee Management & Scheduling System
        </p>

        <p class="welcome-text">
            Select an option from the menu to begin.
        </p>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

