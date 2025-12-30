<?php
session_start();

/* Prüfen, ob Produkt-ID übergeben wurde */
if (!isset($_GET["id"])) {
    echo "Kein Produkt ausgewählt.";
    exit;
}

$productId = (int) $_GET["id"];

/* Produkt aus dem Warenkorb entfernen */
if (isset($_SESSION["cart"][$productId])) {
    unset($_SESSION["cart"][$productId]);
}

/* Zurück zum Warenkorb */
header("Location: cart.php");
exit;