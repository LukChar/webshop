<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/google_oauth.php";

// 1) Error von Google?
if (isset($_GET["error"])) {
    die("Google OAuth Fehler: " . htmlspecialchars($_GET["error"]));
}

// 2) State prüfen
$state = $_GET["state"] ?? "";
if ($state === "" || !isset($_SESSION["google_oauth_state"]) || !hash_equals($_SESSION["google_oauth_state"], $state)) {
    // Debug-Ausgabe (hilft sofort)
    echo "Ungültiger OAuth State.<br><br>";
    echo "GET state: " . htmlspecialchars($state) . "<br>";
    echo "SESSION state: " . htmlspecialchars($_SESSION["google_oauth_state"] ?? "(none)") . "<br>";
    echo "Session ID: " . htmlspecialchars(session_id()) . "<br>";
    exit;
}

// Einmal verwenden
unset($_SESSION["google_oauth_state"]);

// 3) Authorization Code prüfen
$code = $_GET["code"] ?? "";
if ($code === "") {
    die("Kein Authorization Code erhalten.");
}

// 4) Code -> Token tauschen
$postData = [
    "code" => $code,
    "client_id" => GOOGLE_CLIENT_ID,
    "client_secret" => GOOGLE_CLIENT_SECRET,
    "redirect_uri" => GOOGLE_REDIRECT_URI,
    "grant_type" => "authorization_code",
];

$ch = curl_init(GOOGLE_TOKEN_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
]);
$tokenResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($tokenResponse === false || $httpCode !== 200) {
    die("Token request failed. HTTP " . (int)$httpCode . "<br>" . htmlspecialchars((string)$tokenResponse));
}

$tokenData = json_decode($tokenResponse, true);
$accessToken = $tokenData["access_token"] ?? "";
if ($accessToken === "") {
    die("Kein access_token erhalten.");
}

// 5) Userinfo holen
$ch = curl_init(GOOGLE_USERINFO);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $accessToken],
]);
$userResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($userResponse === false || $httpCode !== 200) {
    die("Userinfo request failed. HTTP " . (int)$httpCode . "<br>" . htmlspecialchars((string)$userResponse));
}

$user = json_decode($userResponse, true);

$email = trim($user["email"] ?? "");
$name  = trim($user["name"] ?? "");
$googleSub = trim($user["sub"] ?? ""); // Google User ID

if ($email === "" || $googleSub === "") {
    die("Ungültige Userdaten von Google.");
}

// 6) User in DB finden/erstellen (minimal)
$stmt = $pdo->prepare("SELECT id, role, active FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    if ((int)$existing["active"] !== 1) {
        die("Ihr Konto ist nicht aktiv.");
    }
    $userId = (int)$existing["id"];
    $role   = (string)$existing["role"];
} else {
    // minimaler Insert (passwort leer/NULL je nach Schema)
    $stmt = $pdo->prepare("
        INSERT INTO users (email, name, role, active)
        VALUES (?, ?, 'user', 1)
    ");
    $stmt->execute([$email, $name !== "" ? $name : $email]);
    $userId = (int)$pdo->lastInsertId();
    $role   = "user";
}

// 7) Login Session setzen
$_SESSION["user_id"] = $userId;
$_SESSION["role"] = $role;

// 8) Redirect
header("Location: /webshop/public/index.php");
exit;
