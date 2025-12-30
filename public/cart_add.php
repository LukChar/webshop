<?php
session_start();

/* Prüfen, ob Produkt-ID übergeben wurde */
if (!isset($_POST["product_id"])) {
    header("Location: index.php");
    exit;
}

$productId = (int) $_POST["product_id"];

/* Warenkorb initialisieren */
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

/* Produkt hinzufügen oder Menge erhöhen */
if (isset($_SESSION["cart"][$productId])) {
    $_SESSION["cart"][$productId]++;
} else {
    $_SESSION["cart"][$productId] = 1;
}

/* Zurück zur Startseite */
header("Location: index.php");
exit;