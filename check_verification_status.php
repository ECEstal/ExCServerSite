<?php
require 'config.php';
require 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'unauthenticated']);
    exit;
}

$userId = $_SESSION['user']['id'];

// Fetch current verification status
$stmt = $pdo->prepare("SELECT character_id FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['status' => 'not_found']);
    exit;
}

$isVerified = !empty($user['character_id']);

// Update session if verified
if ($isVerified && empty($_SESSION['user']['character_id'])) {
    $_SESSION['user']['character_id'] = $user['character_id'];
}

echo json_encode([
    'status' => 'ok',
    'verified' => $isVerified
]);
