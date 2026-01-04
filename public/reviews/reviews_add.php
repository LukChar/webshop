<?php
session_start();
require_once "../../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

$productId = isset($_POST["product_id"]) ? (int)$_POST["product_id"] : 0;
$authorName = trim($_POST["author_name"] ?? "");
$rating = isset($_POST["rating"]) ? (int)$_POST["rating"] : -1;
$text = trim($_POST["text"] ?? "");

if ($productId <= 0) {
    header("Location: ../index.php");
    exit;
}

/* VALIDATION */
$errors = [];

if (mb_strlen($authorName) < 2 || mb_strlen($authorName) > 100) {
    $errors[] = "Bitte einen gültigen Namen eingeben (2–100 Zeichen).";
}
if (!is_int($rating) || $rating < 0 || $rating > 5) {
    $errors[] = "Bitte eine Bewertung zwischen 0 und 5 auswählen.";
}
if (mb_strlen($text) < 3) {
    $errors[] = "Bitte einen Rezensionstext eingeben (mind. 3 Zeichen).";
}

if (!empty($errors)) {
    $msg = urlencode(implode(" ", $errors));
    header("Location: ../product.php?id={$productId}&err={$msg}");
    exit;
}

$userId = (int)$_SESSION["user_id"];

/* Optional: nur 1 Review pro User & Produkt */
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$productId, $userId]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // Update existing
    $stmt = $pdo->prepare("
        UPDATE reviews
        SET author_name = ?, rating = ?, text = ?, created_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$authorName, $rating, $text, $existing["id"]]);
} else {
    // Insert new
    $stmt = $pdo->prepare("
        INSERT INTO reviews (product_id, user_id, author_name, rating, text)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$productId, $userId, $authorName, $rating, $text]);
}

header("Location: ../product.php?id={$productId}#reviews");
exit;
