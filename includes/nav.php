<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav>
    <a href="/index.php">Startseite</a>
    |
    <a href="/cart.php">Warenkorb</a>
    |
    <a href="/my_orders.php">Meine Bestellungen</a>

    <?php if (isset($_SESSION["user_id"])): ?>
        |
        <a href="/auth/logout.php">Logout</a>

        <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
            |
            <a href="/admin/dashboard.php">Admin</a>
        <?php endif; ?>

    <?php else: ?>
        |
        <a href="/auth/login.php">Login</a>
        |
        <a href="/auth/register.php">Registrieren</a>
    <?php endif; ?>
</nav>

<hr>