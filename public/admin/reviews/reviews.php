<?php
session_start();
require_once "../includes/db.php";

/* Admin-Check */
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

/* Filter / Sort */
$q = trim($_GET["q"] ?? "");
$sort = $_GET["sort"] ?? "newest";
$allowedSort = ["newest","oldest","rating_desc","rating_asc"];
if (!in_array($sort, $allowedSort, true)) $sort = "newest";

$orderBy = "r.created_at DESC";
if ($sort === "oldest") $orderBy = "r.created_at ASC";
if ($sort === "rating_desc") $orderBy = "r.rating DESC, r.created_at DESC";
if ($sort === "rating_asc") $orderBy = "r.rating ASC, r.created_at DESC";

$params = [];
$where = "1=1";

if ($q !== "") {
    $where .= " AND (p.name LIKE ? OR r.author_name LIKE ? OR r.text LIKE ? OR u.email LIKE ?)";
    $like = "%{$q}%";
    $params = [$like, $like, $like, $like];
}

$stmt = $pdo->prepare("
    SELECT
        r.id, r.product_id, r.user_id, r.author_name, r.rating, r.text, r.helpful, r.created_at,
        p.name AS product_name,
        u.email AS user_email
    FROM reviews r
    JOIN products p ON p.id = r.product_id
    JOIN users u ON u.id = r.user_id
    WHERE {$where}
    ORDER BY {$orderBy}
    LIMIT 200
");
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = trim($_GET["msg"] ?? "");
$err = trim($_GET["err"] ?? "");
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Rezensionen</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>
<body class="bg-gray-50 min-h-screen p-6">

<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Rezensionen (Admin)</h1>
        <a href="index.php" class="text-sm font-bold underline">Zurück</a>
    </div>

    <?php if ($msg): ?>
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <?php if ($err): ?>
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            <?php echo htmlspecialchars($err); ?>
        </div>
    <?php endif; ?>

    <form method="get" class="bg-white p-4 rounded-xl shadow-sm border mb-6 flex flex-col md:flex-row gap-3">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>"
               class="flex-1 rounded-lg border-gray-200"
               placeholder="Suche: Produkt, Autor, Text, User-Mail…">

        <select name="sort" class="rounded-lg border-gray-200" onchange="this.form.submit()">
            <option value="newest" <?php echo $sort==="newest" ? "selected" : ""; ?>>Neueste</option>
            <option value="oldest" <?php echo $sort==="oldest" ? "selected" : ""; ?>>Älteste</option>
            <option value="rating_desc" <?php echo $sort==="rating_desc" ? "selected" : ""; ?>>Rating ↓</option>
            <option value="rating_asc" <?php echo $sort==="rating_asc" ? "selected" : ""; ?>>Rating ↑</option>
        </select>

        <button class="rounded-lg bg-black text-white font-bold px-4 py-2">Filtern</button>
    </form>

    <?php if (empty($reviews)): ?>
        <div class="bg-white p-6 rounded-xl border shadow-sm text-gray-600">
            Keine Rezensionen gefunden.
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                <tr>
                    <th class="text-left p-3">Produkt</th>
                    <th class="text-left p-3">Autor</th>
                    <th class="text-left p-3">Rating</th>
                    <th class="text-left p-3">Hilfreich</th>
                    <th class="text-left p-3">Datum</th>
                    <th class="text-right p-3">Aktion</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reviews as $r): ?>
                    <tr class="border-t">
                        <td class="p-3">
                            <div class="font-bold"><?php echo htmlspecialchars($r["product_name"]); ?></div>
                            <div class="text-xs text-gray-500">PID: <?php echo (int)$r["product_id"]; ?></div>
                        </td>
                        <td class="p-3">
                            <div class="font-bold"><?php echo htmlspecialchars($r["author_name"]); ?></div>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($r["user_email"]); ?></div>
                        </td>
                        <td class="p-3 font-bold"><?php echo (int)$r["rating"]; ?>/5</td>
                        <td class="p-3"><?php echo (int)$r["helpful"]; ?></td>
                        <td class="p-3"><?php echo htmlspecialchars(date("d.m.Y H:i", strtotime($r["created_at"]))); ?></td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <a class="font-bold underline mr-3" href="review_edit.php?id=<?php echo (int)$r["id"]; ?>">Bearbeiten</a>
                            <form action="review_delete.php" method="post" class="inline"
                                  onsubmit="return confirm('Rezension wirklich löschen?');">
                                <input type="hidden" name="id" value="<?php echo (int)$r["id"]; ?>">
                                <button class="font-bold text-red-600 underline">Löschen</button>
                            </form>
                        </td>
                    </tr>
                    <tr class="bg-gray-50 border-t">
                        <td colspan="6" class="p-3 text-gray-700">
                            <?php echo nl2br(htmlspecialchars($r["text"])); ?>
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
