<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav>
    <a href="/webshop/public/index.php">Startseite</a>
    |
    <a href="/webshop/public/cart.php">Warenkorb</a>

    <?php if (isset($_SESSION["user_id"])): ?>
        |
        <a href="/webshop/auth/logout.php">Logout</a>

        <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
            |
            <a href="/webshop/admin/dashboard.php">Admin</a>
        <?php endif; ?>

    <?php else: ?>
        |
        <a href="/webshop/auth/login.php">Login</a>
        |
        <a href="/webshop/auth/register.php">Registrieren</a>
    <?php endif; ?>
</nav>

<hr>