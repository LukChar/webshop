<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

/* Favoriten laden */
$stmt = $pdo->prepare("
    SELECT product_id
    FROM favorites
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

/* Alle Favoriten in den Warenkorb */
foreach ($favorites as $productId) {

    $productId = (int)$productId;

    if ($productId <= 0) {
        continue;
    }

    if (isset($_SESSION["cart"][$productId])) {
        $_SESSION["cart"][$productId]++;
    } else {
        $_SESSION["cart"][$productId] = 1;
    }
}

header("Location: favorites.php");
exit;