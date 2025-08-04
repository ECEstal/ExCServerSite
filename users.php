<?php
require 'config.php';
require 'db.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
$user = $_SESSION['user'];
$isAdmin = $_SESSION['is_admin'] ?? false; // just in case

// Get all users except current
$stmt = $pdo->prepare("SELECT id, username, discriminator, avatar FROM users WHERE id != ?");
$stmt->execute([$user['id']]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
    <link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
        .card {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .user-card {
            cursor: pointer;
            transition: box-shadow 0.2s;
        }
        .user-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-9 py-5 px-4">
            <h2 class="mb-4">Start a Conversation</h2>

            <?php if (empty($users)): ?>
                <p class="text-muted">No other users found.</p>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($users as $u): ?>
                        <div class="col">
                            <div class="card user-card p-3 d-flex align-items-center gap-3">
                                <img src="https://cdn.discordapp.com/avatars/<?= htmlspecialchars($u['id']) ?>/<?= htmlspecialchars($u['avatar']) ?>.png" class="rounded-circle profile-img" alt="Avatar of <?= htmlspecialchars($u['username']) ?>">
                                <div>
                                    <h5 class="mb-1"><?= ucfirst(htmlspecialchars($u['username'])) ?></h5>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>
