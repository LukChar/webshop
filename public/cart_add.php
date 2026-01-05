<?php
session_start();

$productId = isset($_POST["product_id"]) ? (int)$_POST["product_id"] : 0;
$qty = isset($_POST["quantity"]) ? (int)$_POST["quantity"] : 1;

if ($productId <= 0) {
    header("Location: index.php");
    exit;
}

// Menge absichern
if ($qty < 1) $qty = 1;
if ($qty > 99) $qty = 99;

if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if (!isset($_SESSION["cart"][$productId])) {
    $_SESSION["cart"][$productId] = 0;
}

$_SESSION["cart"][$productId] += $qty;

// Zurück: wenn Referer vorhanden, sonst Warenkorb
$back = $_SERVER["HTTP_REFERER"] ?? "cart.php";
header("Location: " . $back);
exit;
