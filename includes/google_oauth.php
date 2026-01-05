<?php
declare(strict_types=1);

// Endpoints / Scopes (unproblematisch zu definieren)
define("GOOGLE_AUTH_URL",  "https://accounts.google.com/o/oauth2/v2/auth");
define("GOOGLE_TOKEN_URL", "https://oauth2.googleapis.com/token");
define("GOOGLE_USERINFO",  "https://www.googleapis.com/oauth2/v3/userinfo");
define("GOOGLE_SCOPES",    "openid email profile");

// 1) Lokale Secrets zuerst laden (damit sie NICHT blockiert werden)
$localSecrets = __DIR__ . "/google_oauth.local.php";
if (file_exists($localSecrets)) {
    require_once $localSecrets;
}

// 2) Fallbacks nur setzen, falls local nicht existiert / nicht gesetzt
if (!defined("GOOGLE_CLIENT_ID")) {
    define("GOOGLE_CLIENT_ID", "");
}
if (!defined("GOOGLE_CLIENT_SECRET")) {
    define("GOOGLE_CLIENT_SECRET", "");
}
if (!defined("GOOGLE_REDIRECT_URI")) {
    define("GOOGLE_REDIRECT_URI", "");
}
