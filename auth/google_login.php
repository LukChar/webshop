<?php
session_start();
require_once __DIR__ . "/../includes/google_oauth.php";

// Hard-Fail wenn Config fehlt (hilft beim Debuggen)
if (GOOGLE_CLIENT_ID === "" || GOOGLE_REDIRECT_URI === "") {
    die("Google OAuth config missing. CLIENT_ID or REDIRECT_URI is empty.");
}

// state gegen CSRF
$state = bin2hex(random_bytes(16));
$_SESSION["google_oauth_state"] = $state;

$params = [
    "client_id" => GOOGLE_CLIENT_ID,
    "redirect_uri" => GOOGLE_REDIRECT_URI,
    "response_type" => "code",
    "scope" => GOOGLE_SCOPES,
    "access_type" => "online",
    "include_granted_scopes" => "true",
    "state" => $state,
    // Optional: immer Account-Auswahl anzeigen
    "prompt" => "select_account",
];

$authUrl = GOOGLE_AUTH_URL . "?" . http_build_query($params);

header("Location: " . $authUrl);
exit;
