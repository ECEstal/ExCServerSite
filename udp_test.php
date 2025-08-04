<?php
// Minimal pure PHP RCON client for Ark (Source RCON protocol)
class Rcon {
    private $socket;
    private $host;
    private $port;
    private $password;
    private $requestId = 1;

    public function __construct($host, $port, $password) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
    }

    private function sendPacket($id, $type, $body) {
        $length = strlen($body) + 10;
        // Packet structure: 4 bytes length, 4 bytes id, 4 bytes type, body, 2 null bytes
        $packet = pack('V3', $length, $id, $type) . $body . "\x00\x00";
        socket_send($this->socket, $packet, strlen($packet), 0);
    }

    private function readPacket() {
        $lengthData = socket_read($this->socket, 4);
        if ($lengthData === false || strlen($lengthData) < 4) {
            throw new Exception("Failed reading packet length");
        }
        $length = unpack('V', $lengthData)[1];
        $data = socket_read($this->socket, $length, PHP_BINARY_READ);
        if ($data === false || strlen($data) < $length) {
            throw new Exception("Failed reading packet data");
        }
        $packet = unpack('Vid/Vtype', substr($data, 0, 8));
        $body = substr($data, 8, -2); // exclude trailing 2 null bytes
        return ['id' => $packet['id'], 'type' => $packet['type'], 'body' => $body];
    }

    public function connect() {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->socket) {
            throw new Exception("Socket creation failed: " . socket_strerror(socket_last_error()));
        }
        $result = socket_connect($this->socket, $this->host, $this->port);
        if (!$result) {
            throw new Exception("Socket connection failed: " . socket_strerror(socket_last_error($this->socket)));
        }

        // Send auth packet
        $this->sendPacket($this->requestId, 3, $this->password);
        $response = $this->readPacket();

        if ($response['id'] == -1) {
            throw new Exception("Authentication failed: Invalid RCON password");
        }
    }

    public function sendCommand($command) {
        $this->sendPacket($this->requestId, 2, $command);
        $response = $this->readPacket();
        return $response['body'];
    }

    public function disconnect() {
        socket_close($this->socket);
    }
}

// Your server list:
$servers = [
    ["name" => "The Island", "ip" => "192.168.1.59", "rcon_port" => 37015, "rcon_password" => "g278535814", "game_port" => 7777, "image" => "images/TheIsland.png"],
    ["name" => "The Center", "ip" => "192.168.1.59", "rcon_port" => 37027, "rcon_password" => "g278535814", "game_port" => 7787, "image" => "images/TheCenter.png"],
    ["name" => "Ragnarok", "ip" => "192.168.1.59", "rcon_port" => 37018, "rcon_password" => "g278535814", "game_port" => 7779, "image" => "images/Ragnarok.png"],
    ["name" => "Aberration", "ip" => "192.168.1.59", "rcon_port" => 37021, "rcon_password" => "g278535814", "game_port" => 7781, "image" => "images/Aberration.png"],
    ["name" => "Extinction", "ip" => "192.168.1.59", "rcon_port" => 37019, "rcon_password" => "g278535814", "game_port" => 7781, "image" => "images/Extinction.png"],
    ["name" => "Astraeos", "ip" => "192.168.1.59", "rcon_port" => 37023, "rcon_password" => "g278535814", "game_port" => 7783, "image" => "images/Astraeos.png"],
    ["name" => "Scorched Earth", "ip" => "192.168.1.59", "rcon_port" => 37029, "rcon_password" => "g278535814", "game_port" => 7783, "image" => "images/ScorchedEarth.png"]
];

$externalIP = '69.10.215.85'; // Your external IP for player connect links

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>ARK Servers Status - RCON Only</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background: #f8f9fa; }
        .server-card { transition: 0.3s ease-in-out; }
        .offline { border: 2px solid #dc3545 !important; background-color: #fff5f5; }
        .server-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 0.25rem 0.25rem 0 0;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-9 py-5 px-4">
            <h1 class="mb-4">ARK Server Status (via raw RCON)</h1>
            <div class="row">
                <?php foreach ($servers as $server): 
                    $isOnline = false;
                    $players = 0;
                    try {
                        $rcon = new Rcon($server['ip'], $server['rcon_port'], $server['rcon_password']);
                        $rcon->connect();
                        $response = $rcon->sendCommand('ListPlayers');
                        $rcon->disconnect();

                        $isOnline = true;
                        // Parse player count from response lines starting with number + ". "
                        if (preg_match_all('/^\d+\.\s+.+$/m', $response, $matches)) {
                            $players = count($matches[0]);
                        }
                    } catch (Exception $e) {
                        $isOnline = false;
                    }
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card server-card shadow-sm <?= $isOnline ? '' : 'offline' ?>">
                        <img src="<?= htmlspecialchars($server['image']) ?>" alt="<?= htmlspecialchars($server['name']) ?>" class="card-img-top server-img">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($server['name']) ?></h5>
                            <p class="card-text">
                                Status: <?= $isOnline ? "<span class='text-success'>Online</span> ($players player" . ($players != 1 ? 's' : '') . ")" : "<span class='text-danger'>Offline</span>" ?>
                            </p>
                            <?php if ($isOnline): ?>
                                <a href="steam://connect/<?= $externalIP ?>:<?= (int)$server['game_port'] ?>" class="btn btn-primary" target="_blank" rel="noopener noreferrer">Connect</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
