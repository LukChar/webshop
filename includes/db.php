<?php

$host = getenv("MYSQLHOST") ?: "localhost";
$db   = getenv("MYSQLDATABASE") ?: "webshop";
$user = getenv("MYSQLUSER") ?: "root";
$pass = getenv("MYSQLPASSWORD") ?: "";
$port = getenv("MYSQLPORT") ?: 3306;

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen");
}