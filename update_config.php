<?php
require 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$configPath = __DIR__ . '/DynamicConfig.ini';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['breeding_rate'], $_POST['taming_rate'], $_POST['gathering_rate'])
) {
    $breeding = floatval($_POST['breeding_rate']);
    $taming = floatval($_POST['taming_rate']);
    $gathering = floatval($_POST['gathering_rate']);
    $xp = isset($_POST['xp_multiplier']) ? floatval($_POST['xp_multiplier']) : null;
    $eggHatch = isset($_POST['egg_hatch_speed_multiplier']) ? floatval($_POST['egg_hatch_speed_multiplier']) : null;
    $layEgg = isset($_POST['lay_egg_interval_multiplier']) ? floatval($_POST['lay_egg_interval_multiplier']) : null;
    $matingInterval = isset($_POST['mating_interval_multiplier']) ? floatval($_POST['mating_interval_multiplier']) : null;

    // Baby cuddle scales with breeding
    $babyCuddle = $breeding > 0 ? round(1 / $breeding, 4) : null;

    if (!file_exists($configPath)) {
        echo json_encode(['success' => false, 'message' => 'Config file not found']);
        exit();
    }

    $configData = file_get_contents($configPath);

    // Replace or add config keys
    $replacements = [
        'BabyMatureSpeedMultiplier' => $breeding,
        'TamingSpeedMultiplier' => $taming,
        'HarvestAmountMultiplier' => $gathering,
        'XPMultiplier' => $xp,
        'BabyCuddleIntervalMultiplier' => $babyCuddle,
        'EggHatchSpeedMultiplier' => $eggHatch,
        'LayEggIntervalMultiplier' => $layEgg,
        'MatingIntervalMultiplier' => $matingInterval
    ];

    foreach ($replacements as $key => $value) {
        if ($value === null) continue;
        if (preg_match("/{$key}=.+/", $configData)) {
            $configData = preg_replace("/{$key}=.+/", "$key=$value", $configData);
        } else {
            $configData .= "\n$key=$value";
        }
    }

    // Save config file
    if (file_put_contents($configPath, $configData) === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to save config file']);
        exit();
    }

    // RCON setup
    $rconPath = 'C:\\users\\administrator\\desktop\\old\\rcon.exe';
    $rconPassword = 'g278535814';
    $ports = [37015, 37018, 37019, 37021, 37023, 37025, 37027, 37029];

    $setLines = [];
    foreach ($replacements as $key => $value) {
        if ($value !== null) {
            $setLines[] = "setconfig $key=$value";
        }
    }

    foreach ($ports as $port) {
        foreach ($setLines as $line) {
            $command = "\"$rconPath\" -a 192.168.1.59:$port -p $rconPassword command \"$line\"";
            exec($command);
        }
    }

    // Send forceupdatedynamicconfig command
    foreach ($ports as $port) {
        $forceUpdateCmd = "\"$rconPath\" -a 192.168.1.59:$port -p $rconPassword command \"forceupdatedynamicconfig\"";
        exec($forceUpdateCmd);
    }

    // Serverchat announcement (only XP, Taming, Breeding)
    $announcement = "serverchat [Server]: Rates updated!";
    $parts = [];
    if ($breeding !== null) $parts[] = "Breeding: $breeding";
    if ($taming !== null) $parts[] = "Taming: $taming";
    if ($xp !== null) $parts[] = "XP: $xp";
    if (!empty($parts)) $announcement .= ' ' . implode(', ', $parts);

    foreach ($ports as $port) {
        $announceCommand = "\"$rconPath\" -a 192.168.1.59:$port -p $rconPassword command \"$announcement\"";
        exec($announceCommand);
    }

    // Log activity in DB
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity, timestamp) VALUES (?, ?, NOW())");
        $userId = $_SESSION['user']['id'] ?? 'system';
        $activity = "Updated rates to: " . implode(', ', $parts);
        $stmt->execute([$userId, $activity]);
    } catch (Exception $e) {
        // Optional internal log
    }

    echo json_encode(['success' => true, 'message' => 'Rates updated, applied, and announced successfully.']);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}
