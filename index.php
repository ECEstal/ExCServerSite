<?php
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login with Discord</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS (local or CDN) -->
    <link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css">
    <!-- Optional: Discord Icon CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #23272a 0%, #18191c 100%);
        }
        .discord-btn {
            background: #5865f2;
            border: none;
        }
        .discord-btn:hover, .discord-btn:focus {
            background: #4752c4;
        }
        .invite-card {
            background: #23272a;
            border: 1px solid #5865f2;
            color: #fff;
            box-shadow: 0 0.5rem 1.2rem #18191c66;
        }
        .glow {
            text-shadow: 0 0 8px #5865f2, 0 0 2px #18191c;
        }
    </style>
</head>
<body class="d-flex flex-column justify-content-center align-items-center min-vh-100">
    <div class="container" style="max-width: 400px;">
        <div class="card invite-card text-center mb-4 p-4">
            <div class="mb-3">
                <i class="bi bi-lock-fill fs-1 text-primary"></i>
            </div>
            <h2 class="glow">Access Blocked</h2>
            <p class="mb-3">
                This site is only available to members of our Discord community.<br>
                Not a member? Join now to get access!
            </p>
            <a href="https://discord.gg/VfzVydH6D4" class="btn btn-outline-primary btn-lg mb-2" target="_blank">
                <i class="bi bi-discord"></i> Join Our Discord
            </a>
        </div>

        <div class="card bg-dark border-secondary shadow text-center p-4">
            <img src="https://cdn-icons-png.flaticon.com/512/5968/5968756.png" alt="Discord Logo" width="70" class="mb-3" style="filter: drop-shadow(0 0 10px #5865f2);">
            <h3 class="mb-3">Login with Discord</h3>
            <a href="https://discord.com/api/oauth2/authorize?<?= http_build_query([
                'client_id' => CLIENT_ID,
                'redirect_uri' => REDIRECT_URI,
                'response_type' => 'code',
                'scope' => OAUTH_SCOPE,
                'prompt' => 'consent'
            ]) ?>" class="btn btn-primary btn-lg discord-btn">
                <i class="bi bi-discord me-2"></i>Login via Discord
            </a>
        </div>
    </div>

    <!-- Bootstrap JS (optional, for interactivity) -->
    <script src="./bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
