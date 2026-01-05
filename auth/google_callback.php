<?php
// /auth/google_callback.php
declare(strict_types=1);

session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/google_oauth.php";

function httpPostForm(string $url, array $data): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
        CURLOPT_TIMEOUT => 15,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) throw new RuntimeException("cURL Fehler: " . $err);

    $json = json_decode($raw, true);
    if (!is_array($json)) throw new RuntimeException("Ungültige Token-Response.");

    if ($code >= 400) {
        $msg = $json["error_description"] ?? ($json["error"] ?? "Token-Request fehlgeschlagen");
        throw new RuntimeException($msg);
    }

    return $json;
}

function httpGetJson(string $url, string $bearer): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $bearer],
        CURLOPT_TIMEOUT => 15,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) throw new RuntimeException("cURL Fehler: " . $err);

    $json = json_decode($raw, true);
    if (!is_array($json)) throw new RuntimeException("Ungültige Userinfo-Response.");

    if ($code >= 400) throw new RuntimeException("Userinfo Request fehlgeschlagen.");

    return $json;
}

try {
    $code  = $_GET["code"] ?? "";
    $state = $_GET["state"] ?? "";

    if ($code === "" || $state === "") {
        throw new RuntimeException("Google Login abgebrochen.");
    }

    if (!isset($_SESSION["oauth_state"]) || !hash_equals($_SESSION["oauth_state"], $state)) {
        throw new RuntimeException("Ungültiger OAuth State.");
    }
    unset($_SESSION["oauth_state"]);

    // Code -> Token
    $token = httpPostForm(GOOGLE_TOKEN_URL, [
        "code" => $code,
        "client_id" => GOOGLE_CLIENT_ID,
        "client_secret" => GOOGLE_CLIENT_SECRET,
        "redirect_uri" => GOOGLE_REDIRECT_URI,
        "grant_type" => "authorization_code",
    ]);

    $accessToken = $token["access_token"] ?? "";
    if ($accessToken === "") throw new RuntimeException("Kein Access Token erhalten.");

    // Userinfo
    $u = httpGetJson(GOOGLE_USERINFO, $accessToken);

    $googleId = trim((string)($u["sub"] ?? ""));
    $email    = trim((string)($u["email"] ?? ""));

    if ($googleId === "" || $email === "") {
        throw new RuntimeException("Unvollständige Google Userdaten.");
    }

    // User finden (google_id oder email)
    $stmt = $pdo->prepare("SELECT id, password, role, active, google_id FROM users WHERE google_id = ? OR email = ? LIMIT 1");
    $stmt->execute([$googleId, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ((int)$user["active"] !== 1) {
            throw new RuntimeException("Ihr Konto ist nicht aktiv.");
        }

        // google_id nachtragen falls leer
        if (empty($user["google_id"])) {
            $up = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $up->execute([$googleId, (int)$user["id"]]);
        }

        $_SESSION["user_id"] = (int)$user["id"];
        $_SESSION["role"] = $user["role"] ?? "user";
    } else {
        // Neues Konto: wir setzen ein zufälliges Passwort-Hash (damit NOT NULL sicher ist)
        $randomPasswordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        // active = 1, role = user
        $ins = $pdo->prepare("
            INSERT INTO users (email, password, role, active, google_id)
            VALUES (?, ?, 'user', 1, ?)
        ");
        $ins->execute([$email, $randomPasswordHash, $googleId]);

        $_SESSION["user_id"] = (int)$pdo->lastInsertId();
        $_SESSION["role"] = "user";
    }

    header("Location: ../public/index.php");
    exit;

} catch (Throwable $e) {
    header("Location: login.php?err=" . urlencode($e->getMessage()));
    exit;
}
