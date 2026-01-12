<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /auth/login.php");
    exit;
}

require_once "../includes/db.php";

$userId = $_SESSION["user_id"];
$addressId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$error = "";
$isEdit = false;

/* Adresse laden (bei Bearbeiten) */
if ($addressId > 0) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM addresses
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$addressId, $userId]);
    $address = $stmt->fetch();

    if (!$address) {
        echo "Adresse nicht gefunden.";
        exit;
    }

    $isEdit = true;
}

/* Formular verarbeiten */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstName   = trim($_POST["first_name"] ?? "");
    $lastName    = trim($_POST["last_name"] ?? "");
    $street      = trim($_POST["street"] ?? "");
    $postalCode  = trim($_POST["postal_code"] ?? "");
    $city        = trim($_POST["city"] ?? "");
    $country     = trim($_POST["country"] ?? "");

    if (
        $firstName === "" ||
        $lastName === "" ||
        $street === "" ||
        $postalCode === "" ||
        $city === "" ||
        $country === ""
    ) {
        $error = "Bitte alle Felder ausfüllen.";
    } else {

        if ($isEdit) {
            $stmt = $pdo->prepare("
                UPDATE addresses
                SET first_name = ?, last_name = ?, street = ?, postal_code = ?, city = ?, country = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $firstName,
                $lastName,
                $street,
                $postalCode,
                $city,
                $country,
                $addressId,
                $userId
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO addresses (user_id, first_name, last_name, street, postal_code, city, country, is_default)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([
                $userId,
                $firstName,
                $lastName,
                $street,
                $postalCode,
                $city,
                $country
            ]);
        }

        header("Location: addresses.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?php echo $isEdit ? "Adresse bearbeiten" : "Neue Adresse"; ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b",
                "background-light": "#f6f8f6",
                "background-dark": "#102216",
                "surface-light": "#ffffff",
                "surface-dark": "#1a2c20",
                "border-light": "#e5e7eb",
                "border-dark": "#2d4234",
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>

<style>
body { min-height: max(884px, 100dvh); }
</style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-[#111813] dark:text-gray-100">

<div class="max-w-md mx-auto min-h-screen flex flex-col">

<!-- HEADER -->
<header class="sticky top-0 z-10 flex items-center justify-between bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md p-4 border-b border-border-light dark:border-border-dark">
    <a href="/addresses.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <h1 class="text-lg font-bold">
        <?php echo $isEdit ? "Adresse bearbeiten" : "Neue Adresse"; ?>
    </h1>
    <div class="size-10"></div>
</header>

<main class="flex-1 px-4 pt-6 pb-28">

<?php if ($error): ?>
    <p class="text-red-600 text-sm mb-4"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post" class="space-y-4">

    <div class="grid grid-cols-2 gap-3">
        <input class="h-12 rounded-lg border p-3"
               name="first_name"
               placeholder="Vorname"
               value="<?php echo htmlspecialchars($address["first_name"] ?? ""); ?>">
        <input class="h-12 rounded-lg border p-3"
               name="last_name"
               placeholder="Nachname"
               value="<?php echo htmlspecialchars($address["last_name"] ?? ""); ?>">
    </div>

    <input class="h-12 w-full rounded-lg border p-3"
           name="street"
           placeholder="Straße & Hausnummer"
           value="<?php echo htmlspecialchars($address["street"] ?? ""); ?>">

    <div class="grid grid-cols-2 gap-3">
        <input class="h-12 rounded-lg border p-3"
               name="postal_code"
               placeholder="PLZ"
               value="<?php echo htmlspecialchars($address["postal_code"] ?? ""); ?>">
        <input class="h-12 rounded-lg border p-3"
               name="city"
               placeholder="Ort"
               value="<?php echo htmlspecialchars($address["city"] ?? ""); ?>">
    </div>

    <input class="h-12 w-full rounded-lg border p-3"
           name="country"
           placeholder="Land"
           value="<?php echo htmlspecialchars($address["country"] ?? "Österreich"); ?>">

    <button type="submit"
            class="w-full h-12 rounded-lg bg-primary text-[#102216] font-bold flex items-center justify-center gap-2">
        <span class="material-symbols-outlined">save</span>
        Speichern
    </button>

</form>

</main>

<?php require_once "../includes/bottom_nav.php"; ?>

</div>
</body>
</html>