<?php
session_start();
require_once "../includes/db.php";

/* Admin-Check */
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo "Zugriff verweigert.";
    exit;
}

/* =========================
   CREATE / UPDATE
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $id = (int)($_POST["id"] ?? 0);

    if ($name !== "") {
        if ($id > 0) {
            /* Update */
            $stmt = $pdo->prepare("
                UPDATE categories
                SET name = ?, description = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $id]);
        } else {
            /* Create */
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, description)
                VALUES (?, ?)
            ");
            $stmt->execute([$name, $description]);
        }
    }

    header("Location: categories.php");
    exit;
}

/* =========================
   DELETE
========================= */
if (isset($_GET["delete"])) {
    $deleteId = (int)$_GET["delete"];

    if ($deleteId > 0) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$deleteId]);
    }

    header("Location: categories.php");
    exit;
}

/* =========================
   EDIT MODE
========================= */
$editCategory = null;
if (isset($_GET["edit"])) {
    $editId = (int)$_GET["edit"];

    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =========================
   LOAD CATEGORIES
========================= */
$stmt = $pdo->query("
    SELECT id, name, description
    FROM categories
    ORDER BY name ASC
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kategorien verwalten</title>

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

<div class="max-w-4xl mx-auto p-6">

    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="index.php"
           class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow hover:bg-gray-50">
            ←
        </a>
        <h1 class="text-2xl font-bold">Kategorien verwalten</h1>
    </div>

    <!-- Formular -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
        <h2 class="text-lg font-bold mb-4">
            <?php echo $editCategory ? "Kategorie bearbeiten" : "Neue Kategorie anlegen"; ?>
        </h2>

        <form method="post" class="space-y-4">
            <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?php echo $editCategory["id"]; ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium mb-1">Name *</label>
                <input
                    type="text"
                    name="name"
                    required
                    class="w-full rounded-lg border-gray-300"
                    value="<?php echo htmlspecialchars($editCategory["name"] ?? ""); ?>"
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Beschreibung</label>
                <textarea
                    name="description"
                    rows="3"
                    class="w-full rounded-lg border-gray-300"
                ><?php echo htmlspecialchars($editCategory["description"] ?? ""); ?></textarea>
            </div>

            <button
                type="submit"
                class="px-6 h-11 rounded-lg bg-primary font-bold text-black"
            >
                <?php echo $editCategory ? "Änderungen speichern" : "Kategorie erstellen"; ?>
            </button>
        </form>
    </div>

    <!-- Tabelle -->
    <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Beschreibung</th>
                    <th class="p-3 text-right">Aktionen</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="3" class="p-4 text-gray-500">
                        Keine Kategorien vorhanden.
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($categories as $cat): ?>
                <tr class="border-t">
                    <td class="p-3 font-medium">
                        <?php echo htmlspecialchars($cat["name"]); ?>
                    </td>
                    <td class="p-3 text-gray-600">
                        <?php echo htmlspecialchars($cat["description"]); ?>
                    </td>
                    <td class="p-3 text-right space-x-3">
                        <a href="categories.php?edit=<?php echo $cat["id"]; ?>"
                           class="text-blue-600 hover:underline">
                            Bearbeiten
                        </a>
                        <a href="categories.php?delete=<?php echo $cat["id"]; ?>"
                           class="text-red-600 hover:underline"
                           onclick="return confirm('Kategorie wirklich löschen?');">
                            Löschen
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>