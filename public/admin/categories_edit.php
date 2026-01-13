<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../../includes/db.php";

/* Admin-Check */
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo "Zugriff verweigert.";
    exit;
}

/* Kategorie-ID */
$categoryId = (int)($_GET["id"] ?? 0);
if ($categoryId <= 0) {
    echo "Kategorie nicht gefunden.";
    exit;
}

/* Kategorie laden */
$stmt = $pdo->prepare("
    SELECT id, name, description
    FROM categories
    WHERE id = ?
");
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    echo "Kategorie nicht gefunden.";
    exit;
}

$error = "";

/* Speichern */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($name === "") {
        $error = "Name darf nicht leer sein.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE categories
            SET name = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $categoryId]);

        header("Location: categories.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kategorie bearbeiten</title>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

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

<div class="max-w-md mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">Kategorie bearbeiten</h1>

    <?php if ($error): ?>
        <div class="mb-4 text-red-600 font-medium">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">

        <label class="block">
            <span class="text-sm font-medium">Name *</span>
            <input
                type="text"
                name="name"
                required
                value="<?php echo htmlspecialchars($category["name"]); ?>"
                class="w-full h-12 rounded-lg border p-3"
            >
        </label>

        <label class="block">
            <span class="text-sm font-medium">Beschreibung</span>
            <textarea
                name="description"
                rows="4"
                class="w-full rounded-lg border p-3"
            ><?php echo htmlspecialchars($category["description"] ?? ""); ?></textarea>
        </label>

        <div class="flex gap-3 pt-4">
            <button
                type="submit"
                class="flex-1 h-12 bg-primary font-bold rounded-lg">
                Speichern
            </button>

            <a href="categories.php"
               class="flex-1 h-12 flex items-center justify-center border rounded-lg">
                Abbrechen
            </a>
        </div>

    </form>

</div>

</body>
</html>