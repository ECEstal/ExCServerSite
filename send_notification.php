<?php
header('Content-Type: application/json');

require 'config.php';
require 'db.php';
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$message = trim($_POST['notification_msg'] ?? '');
$sendAs = $_POST['send_as'] ?? 'user';

if ($message === '') {
    http_response_code(400);
    exit('Message cannot be empty.');
}

$userId = $_SESSION['user']['id'] ?? 'unknown';

if ($sendAs === 'user') {
    // Prefer in-game character name from users.character_id if set and non-empty
    $characterName = $_SESSION['user']['character_id'] ?? null;
    if (!$characterName) {
        // Double-check the DB if session value is missing
        $stmt = $pdo->prepare("SELECT character_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $characterName = $row['character_id'] ?? null;
    }
    $usernameRaw = $characterName ?: ($_SESSION['user']['username'] ?? 'Unknown');
    $username = ucfirst($usernameRaw);
    $prefix = "[Web] $username: ";
} else {
    $prefix = "[System]: ";
}

$fullMessageText = $prefix . $message;

// Use escapeshellarg to safely quote the entire message
$commandString = "serverchat " . $fullMessageText;
$escapedMessage = escapeshellarg($commandString);

// RCON settings
$rconPath = 'C:\\users\\administrator\\desktop\\old\\rcon.exe';
$rconPassword = 'g278535814';
$ports = [37015, 37018, 37019, 37021, 37023, 37025, 37027, 37029];

$outputLog = [];

foreach ($ports as $port) {
    $cmd = "\"$rconPath\" -a 192.168.1.59:$port -p $rconPassword command $escapedMessage";
    $output = [];
    $returnCode = 0;

    exec($cmd . ' 2>&1', $output, $returnCode);
    $outputLog[] = "Server $port: " . ($returnCode === 0 ? "✅ Sent" : "❌ Failed") . "<br>" .
                   "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
}

// Log to activity_log
try {
    $pdo->prepare("INSERT INTO activity_log (user_id, activity, timestamp) VALUES (?, ?, NOW())")
        ->execute([$userId, "Sent Message: " . $fullMessageText]);
} catch (Exception $e) {
    $outputLog[] = "<div class='alert alert-warning'>Logging failed: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// ** New: Insert notification into chat_logs **
try {
    $playerName = ($sendAs === 'user') ? $username : 'System';
    $serverName = 'Web: ';

    $insertStmt = $pdo->prepare("INSERT INTO chat_logs (server_name, player_name, message, timestamp) VALUES (?, ?, ?, UTC_TIMESTAMP())");
    $insertStmt->execute([$serverName, $playerName, $message]);
} catch (Exception $e) {
    $outputLog[] = "<div class='alert alert-warning'>Chat log insert failed: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo json_encode([
    'success' => true,
    'message' => 'Notification sent to all servers.',
    'details' => $outputLog
]);
