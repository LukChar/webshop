<?php
require "../includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email === "" || $password === "") {
        $message = "Bitte alle Felder ausfüllen";
    } else {

        // prüfen, ob E-Mail bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $message = "E-Mail ist bereits registriert";
        } else {

            // Passwort sicher hashen
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // User speichern
            $stmt = $pdo->prepare(
                "INSERT INTO users (email, password, role, active)
                 VALUES (?, ?, 'user', 1)"
            );
            $stmt->execute([$email, $hash]);

            $message = "Registrierung erfolgreich";
        }
    }
}

// Formular laden
require "register_form.php";