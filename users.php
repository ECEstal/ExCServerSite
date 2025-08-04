<?php
require 'config.php';
require 'db.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
$user = $_SESSION['user'];
$isAdmin = $_SESSION['is_admin'] ?? false;

// Get all users except current, include verification status
$stmt = $pdo->prepare("SELECT id, username, discriminator, avatar, verification_status FROM users WHERE id != ?");
$stmt->execute([$user['id']]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
// ... same PHP as before to get $users ...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        #main-content {
            margin-left: 220px;
            max-width: 800px;
            margin-right: auto;
            padding: 2.5rem 2rem;
            min-height: 100vh;
        }
        @media (max-width: 991.98px) {
            #main-content {
                margin-left: 0 !important;
                max-width: 100%;
                padding: 1.5rem 0.5rem;
            }
        }
        #main-content h2 {
            text-align: center;
            font-weight: 700;
        }
        .user-card {
            display: flex;
            align-items: center;
            background: #fff;
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.1rem;
            padding: 1.1rem 1.5rem;
            transition: box-shadow 0.18s;
            min-height: 85px;
        }
        .user-card:hover {
            box-shadow: 0 4px 18px rgba(0,0,0,0.13);
        }
        .profile-img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 50%;
            border: 2.5px solid #e8e8e8;
            margin-right: 1.4rem;
            flex-shrink: 0;
            background: #eee;
        }
        .user-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .user-name {
            font-size: 1.22rem;
            font-weight: 600;
            margin-bottom: 0.25em;
            letter-spacing: 0.01em;
        }
        .verification-badge {
            display: inline-block;
            font-size: 0.99em;
            padding: 0.28em 0.8em;
            border-radius: 12px;
            font-weight: 600;
            margin-top: 0.05em;
            margin-bottom: 0.15em;
        }
        .verified {
            background: #d1e7dd;
            color: #198754;
        }
        .not-verified {
            background: #f8d7da;
            color: #dc3545;
        }
        @media (max-width: 600px) {
            .user-card { padding: 0.7rem 0.8rem; }
            .profile-img { width: 48px; height: 48px; margin-right: 1rem; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div id="main-content">
        <h2 class="mb-4">User List</h2>
        <?php if (empty($users)): ?>
            <p class="text-muted">No other users found.</p>
        <?php else: ?>
            <?php foreach ($users as $u): ?>
                <div class="user-card">
                    <img src="https://cdn.discordapp.com/avatars/<?= htmlspecialchars($u['id']) ?>/<?= htmlspecialchars($u['avatar']) ?>.png"
                        class="profile-img"
                        alt="<?= htmlspecialchars($u['username']) ?>"
                        onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?background=e9ecef&color=6c757d&name=<?= urlencode($u['username']) ?>';"
                    >
                    <div class="user-info">
                        <div class="user-name"><?= ucfirst(htmlspecialchars($u['username'])) ?></div>
                        <?php if (strtolower($u['verification_status']) === 'verified'): ?>
                            <span class="verification-badge verified">
                                <i class="fa-solid fa-circle-check me-1"></i>Verified
                            </span>
                        <?php else: ?>
                            <span class="verification-badge not-verified">
                                <i class="fa-solid fa-circle-xmark me-1"></i><?= ucfirst(htmlspecialchars($u['verification_status'])) ?: 'Not Verified' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>

