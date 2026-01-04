<?php
session_start();
require_once "../../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

$userId    = (int) $_SESSION["user_id"];
$reviewId  = isset($_POST["review_id"]) ? (int) $_POST["review_id"] : 0;
$productId = isset($_POST["product_id"]) ? (int) $_POST["product_id"] : 0;

if ($reviewId <= 0 || $productId <= 0) {
    header("Location: ../index.php");
    exit;
}

/* Review prüfen */
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE id = ? AND product_id = ? LIMIT 1");
$stmt->execute([$reviewId, $productId]);
if (!$stmt->fetch()) {
    header("Location: ../product.php?id={$productId}#reviews");
    exit;
}

$pdo->beginTransaction();

try {
    /* Vote speichern (1x pro User) */
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO review_helpful_votes (review_id, user_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$reviewId, $userId]);

    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE reviews SET helpful = helpful + 1 WHERE id = ?");
        $stmt->execute([$reviewId]);

        $pdo->commit();
        header("Location: ../product.php?id={$productId}#reviews");
        exit;
    }

    /* schon gevotet */
    $pdo->commit();
    $msg = urlencode("Du hast diese Rezension bereits als hilfreich markiert.");
    header("Location: ../product.php?id={$productId}&msg={$msg}#reviews");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $msg = urlencode("Fehler beim Speichern der hilfreichen Bewertung.");
    header("Location: ../product.php?id={$productId}&err={$msg}#reviews");
    exit;
}
