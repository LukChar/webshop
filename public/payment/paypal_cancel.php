<?php
session_start();
require_once __DIR__ . "/../../includes/db.php";

$orderId = $_POST["order_id"] ?? null;
$userId  = $_SESSION["user_id"] ?? null;

if ($orderId && $userId) {
    // order_items zuerst löschen (falls kein FK-CASCADE)
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->execute([(int)$orderId]);

    // order löschen (nur eigene + nur pending)
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([(int)$orderId, (int)$userId]);
}

unset($_SESSION["checkout_address"]);

header("Location: ../cart.php");
exit;
