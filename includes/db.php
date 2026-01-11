<?php

$mysqlUrl = getenv("MYSQL_URL");

if ($mysqlUrl) {
    $db = parse_url($mysqlUrl);

    $host = $db["host"];
    $port = $db["port"] ?? 3306;
    $user = $db["user"];
    $pass = $db["pass"];
    $dbname = ltrim($db["path"], "/");
} else {
    // Local fallback (XAMPP / MAMP)
    $host = "localhost";
    $port = 3306;
    $user = "root";
    $pass = "";
    $dbname = "webshop";
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen");
}