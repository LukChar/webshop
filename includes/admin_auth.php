<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Nicht eingeloggt */
if (!isset($_SESSION["user_id"])) {
    header("Location: /webshop/auth/login.php");
    exit;
}

/* Kein Admin */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: /webshop/public/index.php");
    exit;
}