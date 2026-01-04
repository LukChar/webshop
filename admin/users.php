<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db.php";

/* Admin-Check */
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo "Zugriff verweigert.";
    exit;
}

/* Aktionen verarbeiten */
if (isset($_GET["action"], $_GET["id"])) {

    $action = $_GET["action"];
    $userId = (int)$_GET["id"];

    /* Eigener Account darf nicht geändert werden */
    if ($userId !== $_SESSION["user_id"]) {

        if ($action === "toggle_active") {
            $stmt = $pdo->prepare("
                UPDATE users
                SET active = 1 - active
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
        }

        if ($action === "toggle_role") {
            $stmt = $pdo->prepare("
                UPDATE users
                SET role = CASE
                    WHEN role = 'admin' THEN 'user'
                    ELSE 'admin'
                END
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
        }
    }

    header("Location: users.php");
    exit;
}

/* Alle Benutzer laden */
$stmt = $pdo->query("
    SELECT id, email, role, active
    FROM users
    ORDER BY id ASC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Admin – Benutzerverwaltung</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400&display=swap" rel="stylesheet"/>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b"
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>
</head>

<body class="bg-gray-100 font-display">

<div class="max-w-5xl mx-auto p-6">

    <!-- HEADER -->
    <div class="flex items-center gap-3 mb-6">
        <a href="index.php"
           class="flex size-10 items-center justify-center rounded-full hover:bg-gray-200 transition">
            <span class="material-symbols-outlined text-2xl">arrow_back</span>
        </a>

        <h1 class="text-2xl font-bold">Benutzerverwaltung</h1>
    </div>

    <?php if (empty($users)): ?>

        <p class="text-gray-500">Keine Benutzer gefunden.</p>

    <?php else: ?>

        <div class="overflow-x-auto bg-white rounded-xl shadow-sm border">

            <table class="w-full border-collapse">
                <thead class="bg-gray-50 text-left text-sm">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">E-Mail</th>
                        <th class="p-3">Rolle</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Aktionen</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($users as $user): ?>

                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3 font-medium">
                            <?php echo $user["id"]; ?>
                        </td>

                        <td class="p-3">
                            <?php echo htmlspecialchars($user["email"]); ?>
                        </td>

                        <td class="p-3">
                            <span class="inline-block_toggle
                                <?php echo $user["role"] === "admin"
                                    ? "text-purple-600 font-semibold"
                                    : "text-gray-700"; ?>">
                                <?php echo htmlspecialchars($user["role"]); ?>
                            </span>
                        </td>

                        <td class="p-3">
                            <?php if ((int)$user["active"] === 1): ?>
                                <span class="text-green-600 font-semibold">aktiv</span>
                            <?php else: ?>
                                <span class="text-red-600 font-semibold">gesperrt</span>
                            <?php endif; ?>
                        </td>

                        <td class="p-3 space-x-3">
                            <?php if ($user["id"] !== $_SESSION["user_id"]): ?>

                                <a href="?action=toggle_active&id=<?php echo $user["id"]; ?>"
                                   class="text-sm font-medium text-primary hover:underline">
                                    <?php echo $user["active"] ? "Sperren" : "Aktivieren"; ?>
                                </a>

                                <a href="?action=toggle_role&id=<?php echo $user["id"]; ?>"
                                   class="text-sm font-medium text-gray-700 hover:underline">
                                    Rolle ändern
                                </a>

                            <?php else: ?>
                                <span class="text-xs text-gray-400 italic">
                                    Eigener Account
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

        </div>

    <?php endif; ?>

</div>

</body>
</html>