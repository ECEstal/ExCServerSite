<?php

require 'config.php';
require 'db.php';
session_start();

if (!isset($_GET['code'])) exit("No code provided");

function exchangeToken($code) {
    $data = [
        'client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => REDIRECT_URI,
        'scope' => OAUTH_SCOPE
    ];

    $ch = curl_init('https://discord.com/api/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    $response = curl_exec($ch);

    if ($response === false) {
        die('Curl error: ' . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        die("Discord token endpoint returned HTTP code $httpCode. Response: $response");
    }

    return json_decode($response, true);
}

function apiRequest($url, $access_token) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

$token = exchangeToken($_GET['code']);
if (!isset($token['access_token'])) {
    exit('Token exchange failed. Response: ' . htmlspecialchars(json_encode($token)));
}

$user = apiRequest("https://discord.com/api/users/@me", $token['access_token']);
$guild = apiRequest("https://discord.com/api/users/@me/guilds/" . GUILD_ID . "/member", $token['access_token']);

// Only check they are in the server; no role required
if (!$user || !$guild) {
    die("Access denied: not in server.");
}

// Still set admin status if needed for your app
$isAdmin = in_array(ADMIN_ROLE_ID, $guild['roles'] ?? []);

// SAFE INSERT/UPDATE that preserves character_id and steam_id
$stmt = $pdo->prepare("
    INSERT INTO users (
        id, username, discriminator, avatar, email, access_token, refresh_token, token_expires
    ) VALUES (
        :id, :username, :discriminator, :avatar, :email, :access_token, :refresh_token, :token_expires
    )
    ON DUPLICATE KEY UPDATE
        username = VALUES(username),
        discriminator = VALUES(discriminator),
        avatar = VALUES(avatar),
        email = VALUES(email),
        access_token = VALUES(access_token),
        refresh_token = VALUES(refresh_token),
        token_expires = VALUES(token_expires)
");

$stmt->execute([
    'id' => (string)$user['id'],  // force as string
    'username' => $user['username'],
    'discriminator' => $user['discriminator'],
    'avatar' => $user['avatar'],
    'email' => $user['email'],
    'access_token' => $token['access_token'],
    'refresh_token' => $token['refresh_token'],
    'token_expires' => time() + $token['expires_in']
]);

// Fetch character_id and steam_id for this user (if set)
$charStmt = $pdo->prepare("SELECT character_id, steam_id FROM users WHERE id = ?");
$charStmt->execute([$user['id']]);
$row = $charStmt->fetch(PDO::FETCH_ASSOC);

$user['character_id'] = $row['character_id'] ?? null;
$user['steam_id']     = $row['steam_id'] ?? null;

$_SESSION['user'] = $user;
$_SESSION['is_admin'] = $isAdmin;

$stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");
$stmt->execute([$user['id'], 'User logged in']);

header("Location: dashboard.php");
exit();
