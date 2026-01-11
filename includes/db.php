<?php
var_dump($_ENV);
die();

$host = getenv("MYSQL_HOST") ?: "localhost";
$db   = getenv("MYSQL_DATABASE") ?: "webshop";
$user = getenv("MYSQL_USER") ?: "root";
$pass = getenv("MYSQL_PASSWORD") ?: "";
$port = getenv("MYSQL_PORT") ?: 3306;

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen");
}