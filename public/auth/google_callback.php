<?php
// /auth/google_callback.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../../includes/db.php";
require_once __DIR__ . "/../../includes/google_oauth.php";

/* 1) Google Error? */
if (isset($_GET["error"]) && $_GET["error"] !== "") {
    $msg = trim((string)$_GET["error"]);
    header("Location: login.php?err=" . urlencode("Google Login abgebrochen: " . $msg));
    exit;
}

/* 2) State prüfen */
$state = $_GET["state"] ?? "";
if (
    $state === "" ||
    !isset($_SESSION["google_oauth_state"]) ||
    !hash_equals((string)$_SESSION["google_oauth_state"], (string)$state)
) {
    header("Location: login.php?err=" . urlencode("Ungültiger OAuth State."));
    exit;
}
unset($_SESSION["google_oauth_state"]);

/* 3) Code prüfen */
$code = $_GET["code"] ?? "";
if ($code === "") {
    header("Location: login.php?err=" . urlencode("Kein Authorization Code erhalten."));
    exit;
}

/* 4) Token holen */
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
    CURLOPT_TIMEOUT => 15,
]);
$tokenResponse = curl_exec($ch);
$tokenHttp = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$tokenErr = curl_error($ch);
curl_close($ch);

if ($tokenResponse === false || $tokenHttp !== 200) {
    $detail = $tokenResponse !== false ? $tokenResponse : $tokenErr;
    header("Location: login.php?err=" . urlencode("Token request failed (HTTP {$tokenHttp}). " . $detail));
    exit;
}

$tokenData = json_decode((string)$tokenResponse, true);
$accessToken = $tokenData["access_token"] ?? "";
if ($accessToken === "") {
    header("Location: login.php?err=" . urlencode("Kein access_token erhalten."));
    exit;
}

/* 5) Userinfo holen */
$ch = curl_init(GOOGLE_USERINFO);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $accessToken],
    CURLOPT_TIMEOUT => 15,
]);
$userResponse = curl_exec($ch);
$userHttp = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$userErr = curl_error($ch);
curl_close($ch);

if ($userResponse === false || $userHttp !== 200) {
    $detail = $userResponse !== false ? $userResponse : $userErr;
    header("Location: login.php?err=" . urlencode("Userinfo request failed (HTTP {$userHttp}). " . $detail));
    exit;
}

$user = json_decode((string)$userResponse, true);
$email = trim((string)($user["email"] ?? ""));
$googleSub = trim((string)($user["sub"] ?? ""));

if ($email === "" || $googleSub === "") {
    header("Location: login.php?err=" . urlencode("Ungültige Userdaten von Google."));
    exit;
}

/*
 * 6) User finden oder erstellen
 * Annahmen: users hat mind. Spalten: id, email, password, role, active
 * (kein "name" notwendig)
 */
$stmt = $pdo->prepare("SELECT id, role, active FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    if ((int)$existing["active"] !== 1) {
        header("Location: login.php?err=" . urlencode("Ihr Konto ist nicht aktiv."));
        exit;
    }
    $userId = (int)$existing["id"];
    $role = (string)$existing["role"];
} else {
    // Password ist NOT NULL -> Dummy-Hash setzen
    $dummyPasswordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, role, active)
        VALUES (?, ?, 'user', 1)
    ");
    $stmt->execute([$email, $dummyPasswordHash]);

    $userId = (int)$pdo->lastInsertId();
    $role = "user";
}

/* 7) Session setzen */
$_SESSION["user_id"] = $userId;
$_SESSION["role"] = $role;

/* 8) Redirect */
header("Location: /index.php");
exit;
