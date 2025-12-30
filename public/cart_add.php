<?php
session_start();

/* Prüfen, ob Produkt-ID übergeben wurde */
if (!isset($_GET["id"])) {
    echo "Kein Produkt ausgewählt.";
    exit;
}

$productId = (int) $_GET["id"];

/* Warenkorb initialisieren */
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

/* Produkt zur Session hinzufügen */
if (isset($_SESSION["cart"][$productId])) {
    $_SESSION["cart"][$productId]++;
} else {
    $_SESSION["cart"][$productId] = 1;
}

/* Zurück zur Startseite */
header("Location: index.php");
exit;