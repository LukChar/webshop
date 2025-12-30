<?php
session_start();

/* Prüfen, ob Parameter vorhanden sind */
if (!isset($_GET["id"]) || !isset($_GET["action"])) {
    header("Location: cart.php");
    exit;
}

$productId = (int) $_GET["id"];
$action = $_GET["action"];

/* Warenkorb vorhanden? */
if (!isset($_SESSION["cart"][$productId])) {
    header("Location: cart.php");
    exit;
}

/* Menge ändern */
if ($action === "plus") {
    $_SESSION["cart"][$productId]++;
}

if ($action === "minus") {
    $_SESSION["cart"][$productId]--;

    if ($_SESSION["cart"][$productId] <= 0) {
        unset($_SESSION["cart"][$productId]);
    }
}

/* Zurück zum Warenkorb */
header("Location: cart.php");
exit;