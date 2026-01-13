<?php
// admin/reviews/reviews_edit.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../../includes/admin_auth.php";
require "../../includes/db.php";

$message = "";
$error = "";

/* Review-ID */
$reviewId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($reviewId <= 0) {
    header("Location: ../products.php");
    exit;
}

/* Review laden */
$stmt = $pdo->prepare("
    SELECT r.*, p.name AS product_name
    FROM reviews r
    JOIN products p ON p.id = r.product_id
    WHERE r.id = ?
    LIMIT 1
");
$stmt->execute([$reviewId]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    header("Location: ../products.php");
    exit;
}

$productId = (int)$review["product_id"];

/* Speichern */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_review"])) {

    $authorName = trim($_POST["author_name"] ?? "");
    $rating = isset($_POST["rating"]) ? (int)$_POST["rating"] : -1;
    $text = trim($_POST["text"] ?? "");

    if ($authorName === "" || $text === "") {
        $error = "Bitte Name und Text ausfüllen.";
    } elseif ($rating < 0 || $rating > 5) {
        $error = "Bewertung muss zwischen 0 und 5 liegen.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE reviews
            SET author_name = ?, rating = ?, text = ?
            WHERE id = ?
        ");
        $stmt->execute([$authorName, $rating, $text, $reviewId]);
        $message = "Rezension gespeichert.";

        $stmt = $pdo->prepare("
            SELECT r.*, p.name AS product_name
            FROM reviews r
            JOIN products p ON p.id = r.product_id
            WHERE r.id = ?
            LIMIT 1
        ");
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rezension bearbeiten</title>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#13ec5b",
                bgLight: "#f6f8f6",
                bgDark: "#102216",
                surface: "#ffffff"
            },
            fontFamily: {
                display: ["Inter", "sans-serif"]
            }
        }
    }
}
</script>
</head>

<body class="bg-bgLight font-display text-[#111813]">
<div class="max-w-md mx-auto min-h-screen pb-24">

    <!-- HEADER -->
    <header class="sticky top-0 bg-bgLight/95 backdrop-blur border-b px-4 py-4 flex items-center gap-3">
        <a href="../product_edit.php?id=<?php echo (int)$productId; ?>#reviews"
           class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold truncate">Rezension bearbeiten</h1>
    </header>

    <!-- CONTENT -->
    <div class="px-4 mt-6 space-y-6">

        <!-- Info -->
        <div class="bg-surface rounded-xl p-4 shadow-sm">
            <p class="text-sm text-gray-500">Produkt</p>
            <p class="font-bold truncate"><?php echo htmlspecialchars($review["product_name"] ?? ""); ?></p>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-50 text-green-700 p-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <div class="bg-surface rounded-xl p-4 shadow-sm">
            <form method="post" class="space-y-4">
                <input type="hidden" name="save_review" value="1">

                <label class="block">
                    <span class="text-sm font-medium">Autor Name</span>
                    <input type="text" name="author_name" required
                           value="<?php echo htmlspecialchars($review["author_name"] ?? ""); ?>"
                           class="w-full h-12 rounded-lg border p-3">
                </label>

                <label class="block">
                    <span class="text-sm font-medium">Bewertung (0–5)</span>
                    <select name="rating" required class="w-full h-12 rounded-lg border p-3">
                        <?php for ($i=0; $i<=5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ((int)$review["rating"] === $i) ? "selected" : ""; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm font-medium">Text</span>
                    <textarea name="text" rows="5" required
                              class="w-full rounded-lg border p-3"><?php
                        echo htmlspecialchars($review["text"] ?? "");
                    ?></textarea>
                </label>

                <button type="submit"
                        class="w-full h-12 bg-primary font-bold rounded-lg text-[#102216]">
                    Speichern
                </button>

                <a href="../product_edit.php?id=<?php echo (int)$productId; ?>#reviews"
                   class="block w-full h-12 text-center rounded-lg border font-bold leading-[3rem] hover:bg-gray-50">
                    Abbrechen
                </a>
            </form>
        </div>

        <!-- DELETE -->
        <div class="bg-surface rounded-xl p-4 shadow-sm">
            <form action="reviews_delete.php" method="post"
                  onsubmit="return confirm('Rezension wirklich löschen?');">
                <input type="hidden" name="id" value="<?php echo (int)$reviewId; ?>">
                <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">

                <button class="w-full h-12 rounded-lg border border-red-200 bg-red-50 text-red-700 font-bold hover:bg-red-100">
                    Löschen
                </button>
            </form>
        </div>

    </div>
</div>
</body>
</html>
