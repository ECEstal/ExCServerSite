<?php
session_start();
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$_COOKIE['remember_token']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['is_admin'] = in_array(ADMIN_ROLE_ID, $user['roles'] ?? []);
        // Optionally, regenerate session ID
        session_regenerate_id(true);
    }
}
?>