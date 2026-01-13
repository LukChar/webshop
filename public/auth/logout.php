<?php
session_start();

/* Alle Session-Variablen löschen */
$_SESSION = [];

/* Session zerstören */
session_destroy();

/* Zur Startseite weiterleiten */
header("Location: /index.php");
exit;