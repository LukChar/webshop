<?php
// /includes/google_oauth.php
declare(strict_types=1);

/**
 * Commit-safe config.
 * Echte Werte liegen lokal in: /includes/google_oauth.local.php (nicht versioniert)
 */

const GOOGLE_AUTH_URL  = "https://accounts.google.com/o/oauth2/v2/auth";
const GOOGLE_TOKEN_URL = "https://oauth2.googleapis.com/token";
const GOOGLE_USERINFO  = "https://www.googleapis.com/oauth2/v3/userinfo";
const GOOGLE_SCOPES    = "openid email profile";

// Default Platzhalter (damit es nicht crasht, falls local file fehlt)
if (!defined("GOOGLE_CLIENT_ID")) {
    define("GOOGLE_CLIENT_ID", "");
}
if (!defined("GOOGLE_CLIENT_SECRET")) {
    define("GOOGLE_CLIENT_SECRET", "");
}
if (!defined("GOOGLE_REDIRECT_URI")) {
    define("GOOGLE_REDIRECT_URI", "");
}

// Lokale Secrets nachladen (nur lokal vorhanden)
$local = __DIR__ . "/google_oauth.local.php";
if (file_exists($local)) {
    require_once $local;
}
