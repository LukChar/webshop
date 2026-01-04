<?php
// /auth/google_login.php

declare(strict_types=1);

session_start();
require_once __DIR__ . "/../includes/google_oauth.php";

$state = bin2hex(random_bytes(16));
$_SESSION["oauth_state"] = $state;

$params = [
    "client_id" => GOOGLE_CLIENT_ID,
    "redirect_uri" => GOOGLE_REDIRECT_URI,
    "response_type" => "code",
    "scope" => GOOGLE_SCOPES,
    "state" => $state,
    "access_type" => "online",
    "prompt" => "select_account",
];

header("Location: " . GOOGLE_AUTH_URL . "?" . http_build_query($params));
exit;
