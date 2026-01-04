<?php
session_start();
require_once "../../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

$userId     = (int)$_SESSION["user_id"];
$productId  = isset($_POST["product_id"]) ? (int)$_POST["product_id"] : 0;
$rating     = isset($_POST["rating"]) ? (int)$_POST["rating"] : -1;
$text       = trim($_POST["text"] ?? "");
$authorName = trim($_POST["author_name"] ?? "");

if ($productId <= 0) {
    header("Location: ../index.php");
    exit;
}

if ($rating < 0 || $rating > 5) {
    header("Location: ../product.php?id=$productId&err=rating#reviews");
    exit;
}

if (mb_strlen($text) < 3) {
    header("Location: ../product.php?id=$productId&err=text#reviews");
    exit;
}

/* Name Pflicht */
$authorName = preg_replace('/\s+/', ' ', $authorName); // Mehrfach-Spaces normalisieren
if (mb_strlen($authorName) < 2 || mb_strlen($authorName) > 100) {
    header("Location: ../product.php?id=$productId&err=name#reviews");
    exit;
}

/* Produkt prüfen */
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$productId]);
if (!$stmt->fetch()) {
    header("Location: ../index.php");
    exit;
}

/* Review speichern oder aktualisieren */
$stmt = $pdo->prepare("
    INSERT INTO reviews (product_id, user_id, author_name, rating, text)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        author_name = VALUES(author_name),
        rating = VALUES(rating),
        text = VALUES(text),
        created_at = CURRENT_TIMESTAMP
");
$stmt->execute([$productId, $userId, $authorName, $rating, $text]);

header("Location: ../product.php?id=$productId#reviews");
exit;
