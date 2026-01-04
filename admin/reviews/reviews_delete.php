<?php
// admin/reviews/reviews_delete.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../../includes/admin_auth.php";
require "../../includes/db.php";

$reviewId  = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
$productId = isset($_POST["product_id"]) ? (int)$_POST["product_id"] : 0;

if ($reviewId <= 0 || $productId <= 0) {
    header("Location: ../products.php");
    exit;
}

/* Review löschen */
$stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND product_id = ? LIMIT 1");
$stmt->execute([$reviewId, $productId]);

/* Votes mitlöschen passiert automatisch wenn FK ON DELETE CASCADE gesetzt ist
   (falls nicht: optional manuell löschen)
*/
// $stmt = $pdo->prepare("DELETE FROM review_helpful_votes WHERE review_id = ?");
// $stmt->execute([$reviewId]);

header("Location: ../product_edit.php?id=$productId#reviews");
exit;
