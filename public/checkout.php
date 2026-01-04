<?php
session_start();

/* Login erforderlich */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* Warenkorb darf nicht leer sein */
if (empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

/* Direkt zur Lieferadresse */
header("Location: checkout_delivery.php");
exit;
