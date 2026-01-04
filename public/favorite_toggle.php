<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$productId = (int)($_POST["product_id"] ?? 0);

if ($productId <= 0) {
    header("Location: index.php");
    exit;
}

/* Prüfen ob Favorit existiert */
$stmt = $pdo->prepare("
    SELECT id
    FROM favorites
    WHERE user_id = ? AND product_id = ?
");
$stmt->execute([$userId, $productId]);

if ($stmt->fetch()) {

    /* Entfernen */
    $stmt = $pdo->prepare("
        DELETE FROM favorites
        WHERE user_id = ? AND product_id = ?
    ");
    $stmt->execute([$userId, $productId]);

} else {

    /* Hinzufügen */
    $stmt = $pdo->prepare("
        INSERT INTO favorites (user_id, product_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$userId, $productId]);
}

header("Location: " . ($_SERVER["HTTP_REFERER"] ?? "index.php"));
exit;