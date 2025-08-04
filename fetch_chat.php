<?php
require 'db.php';

header('Content-Type: application/json');

date_default_timezone_set('UTC');

try {
    $since = isset($_GET['since']) ? (float)$_GET['since'] : 0;
    $timeout = 20;
    $start = microtime(true);

    do {
        // Convert UNIX timestamp to MySQL datetime using FROM_UNIXTIME in SQL instead of PHP conversion
        $stmt = $pdo->prepare("
            SELECT player_name, message, UNIX_TIMESTAMP(timestamp) as timestamp
            FROM chat_logs
            WHERE timestamp > FROM_UNIXTIME(:since)
            ORDER BY timestamp ASC
            LIMIT 50
        ");

        $stmt->execute([':since' => $since]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0) {
            echo json_encode($results);
            exit;
        }

        usleep(500000);

    } while ((microtime(true) - $start) < $timeout);

    echo json_encode([]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch chat logs', 'details' => $e->getMessage()]);
}
