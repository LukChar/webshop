<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db.php";

$error = "";

/* Login verarbeiten */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Bitte E-Mail und Passwort eingeben.";
    } else {

        $stmt = $pdo->prepare("
            SELECT id, password, role, active
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user["password"])) {
            $error = "E-Mail oder Passwort ist falsch.";
        } elseif ((int)$user["active"] !== 1) {
            $error = "Ihr Konto ist nicht aktiv.";
        } else {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["role"] = $user["role"];

            header("Location: ../public/index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="de">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

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
                "surface-dark": "#1a2e22",
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>
</head>

<body class="bg-background-light dark:bg-background-dark min-h-screen flex items-center justify-center p-4">

<div class="relative flex w-full max-w-[480px] flex-col bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm overflow-hidden">

<!-- Top Bar -->
<div class="flex items-center p-4 justify-between">
    <a href="../public/index.php"
       class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-white/10">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <h2 class="text-lg font-bold flex-1 text-center pr-10">Mein Konto</h2>
</div>

<!-- Headline -->
<h1 class="text-2xl font-bold text-center mt-2">Willkommen zurück!</h1>
<p class="text-sm text-gray-500 text-center mb-6">
    Logge dich ein für die besten Angebote deines Lebens.
</p>

<!-- Segmented Tabs -->
<div class="flex px-6 pb-6">
    <div class="flex h-12 flex-1 items-center justify-center rounded-lg bg-[#f0f4f2] dark:bg-white/10 p-1">

        <!-- Anmelden (aktiv) -->
        <div
            class="flex h-full grow items-center justify-center overflow-hidden rounded-md px-2
                   bg-white dark:bg-surface-dark shadow
                   text-[#111813] dark:text-white text-sm font-semibold">
            Anmelden
        </div>

        <!-- Registrieren (Link) -->
        <a href="register.php"
           class="flex h-full grow items-center justify-center overflow-hidden rounded-md px-2
                  text-gray-500 dark:text-gray-400
                  hover:text-[#111813] dark:hover:text-white
                  text-sm font-medium transition-colors">
            Registrieren
        </a>

    </div>
</div>

<?php if ($error): ?>
    <p class="text-red-600 text-sm text-center mb-4 px-6">
        <?php echo htmlspecialchars($error); ?>
    </p>
<?php endif; ?>

<!-- Login Form -->
<form method="post" class="flex flex-col gap-4 px-6 pb-6">

<label>
    <span class="text-sm font-medium">E-Mail Adresse</span>
    <input
        type="email"
        name="email"
        required
        class="w-full h-12 rounded-lg border p-3"
        placeholder="name@email.at"
        value="<?php echo htmlspecialchars($email ?? ""); ?>"
    >
</label>

<label>
    <span class="text-sm font-medium">Passwort</span>
    <input
        type="password"
        name="password"
        required
        class="w-full h-12 rounded-lg border p-3"
        placeholder="Dein Passwort"
    >
</label>

<button
    type="submit"
    class="w-full h-12 rounded-lg bg-primary font-bold text-[#102216]">
    Anmelden
</button>

</form>

</div>
</body>
</html>