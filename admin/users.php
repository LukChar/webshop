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
<title>Benutzerverwaltung</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
    body {
        font-family: Arial, sans-serif;
        background: #f6f8f6;
        padding: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
    }
    th, td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    th {
        background: #eeeeee;
    }
    .inactive {
        color: red;
        font-weight: bold;
    }
    .active {
        color: green;
        font-weight: bold;
    }
    .actions a {
        margin-right: 10px;
        text-decoration: none;
        font-weight: bold;
    }
</style>
</head>

<body>

<h1>Benutzerverwaltung</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>E-Mail</th>
            <th>Rolle</th>
            <th>Status</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>

    <?php if (empty($users)): ?>
        <tr>
            <td colspan="5">Keine Benutzer gefunden.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user["id"]; ?></td>
                <td><?php echo htmlspecialchars($user["email"]); ?></td>
                <td><?php echo htmlspecialchars($user["role"]); ?></td>
                <td>
                    <?php if ((int)$user["active"] === 1): ?>
                        <span class="active">aktiv</span>
                    <?php else: ?>
                        <span class="inactive">gesperrt</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <?php if ($user["id"] !== $_SESSION["user_id"]): ?>

                        <a href="?action=toggle_active&id=<?php echo $user["id"]; ?>">
                            <?php echo $user["active"] ? "Sperren" : "Aktivieren"; ?>
                        </a>

                        <a href="?action=toggle_role&id=<?php echo $user["id"]; ?>">
                            Rolle ändern
                        </a>

                    <?php else: ?>
                        <em>Eigener Account</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    </tbody>
</table>

<p style="margin-top: 20px;">
    <a href="index.php">← Zurück zum Admin Dashboard</a>
</p>

</body>
</html>