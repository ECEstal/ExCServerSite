<?php
session_start();

$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// --- Minimal pure PHP RCON client for Ark (Source RCON protocol) ---
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
        $body = substr($data, 8, -2);
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

// --- Server definitions ---
$servers = [
    ["name" => "The Island", "ip" => "192.168.1.59", "rcon_port" => 37015, "rcon_password" => "g278535814", "query_port" => 27015, "image" => "images/TheIsland.png", "description" => "The original ARK map featuring a diverse range of biomes."],
    ["name" => "The Center", "ip" => "192.168.1.59", "rcon_port" => 37027, "rcon_password" => "g278535814", "query_port" => 27027, "image" => "images/TheCenter.png", "description" => "A richly detailed floating island map with many hidden caves."],
    ["name" => "Ragnarok", "ip" => "192.168.1.59", "rcon_port" => 37018, "rcon_password" => "g278535814", "query_port" => 27017, "image" => "images/Ragnarok.png", "description" => "A massive map combining multiple biomes and fantasy elements."],
    ["name" => "Aberration", "ip" => "192.168.1.59", "rcon_port" => 37021, "rcon_password" => "g278535814", "query_port" => 27021, "image" => "images/Aberration.png", "description" => "A malfunctioning ARK with hazardous environments and unique creatures."],
    ["name" => "Extinction", "ip" => "192.168.1.59", "rcon_port" => 37019, "rcon_password" => "g278535814", "query_port" => 27019, "image" => "images/Extinction.png", "description" => "Set on Earth, Extinction blends nature with high-tech threats."],
    ["name" => "Astraeos", "ip" => "192.168.1.59", "rcon_port" => 37023, "rcon_password" => "g278535814", "query_port" => 27023, "image" => "images/Astraeos.png", "description" => "A custom map with mysterious regions and experimental designs."],
    ["name" => "Scorched Earth", "ip" => "192.168.1.59", "rcon_port" => 37029, "rcon_password" => "g278535814", "query_port" => 28015, "image" => "images/ScorchedEarth.png", "description" => "A harsh desert map featuring sandstorms and wyverns."],
    ["name" => "Club Ark", "ip" => "192.168.1.59", "rcon_port" => 37025, "rcon_password" => "g278535814", "query_port" => 27025, "image" => "images/ClubArk.png", "description" => "A fun, party-themed map for casual and social gameplay."]
];

$externalIP = '69.10.215.85';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Server Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .server-card {
            transition: 0.3s;
            cursor: pointer;
            position: relative;
            min-height: 340px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .server-card:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        }
        .offline { border: 2px solid #dc3545 !important; background-color: #fff5f5; }
        .server-img {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }
        .tooltip-custom {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #343a40;
            color: #fff;
            padding: 8px 12px;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.2s;
            pointer-events: none;
            z-index: 10;
        }
        .server-card:hover .tooltip-custom { opacity: 1; }
        @media (max-width: 991px) {
            .server-img { height: 120px; }
        }
        @media (max-width: 767px) {
            .server-card { margin-bottom: 1.2rem; }
        }
		html, body {
    max-width: 100vw;
    overflow-x: hidden;
}

#main-content {
    /* Keep the sidebar offset on desktop */
    margin-left: 220px;
    /* Center the content area, set a max-width */
    max-width: 3200px;
    margin-right: auto;
    margin-top: 0;
    margin-bottom: 0;
    padding: 2.5rem 2rem;
    min-height: 100vh;
    background: none;
}

/* On mobile, remove left margin and allow full width */
@media (max-width: 991.98px) {
    #main-content {
        margin-left: 0 !important;
        margin-right: 0 !important;
        max-width: 100%;
        padding: 1.5rem 0.5rem;
    }
}

        body { background-color: #f8f9fa; }
        .btn-icon { display: inline-flex; align-items: center; gap: 0.5rem; }
        .card-header { background-color: #ffc107 !important; color: #212529 !important; font-weight: 600; }
        .user-welcome { font-weight: 500; margin-bottom: 1.5rem; }
        textarea { resize: vertical; }
        .btn-primary:hover { background-color: #004085; border-color: #003766; }
        #chatLogBox {
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 1rem;
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.9rem;
            margin-bottom: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        #lastRefreshed {
            font-style: italic;
            font-size: 0.8rem;
            color: #555;
            margin-top: 4px;
            user-select: none;
        }
        /* Ensure main content is shifted right on desktop, not covered by sidebar */
        @media (min-width: 992px) {
            #main-content {
                margin-left: 220px;
            }
        }
        @media (max-width: 991.98px) {
            #main-content {
                margin-left: 0 !important;
            }
        }
		#main-content h1 {
    text-align: center;
    font-weight: 700;
}
    </style>
</head>
<body>
        <?php include 'sidebar.php'; ?>

<div id="main-content">
            <h1 class="mb-4"><i class="fa-solid fa-server me-2"></i>ARK Server Status</h1>
            <div class="row g-4">
                <?php foreach ($servers as $server): 
                    $isOnline = false;
                    $players = 0;
                    try {
                        $rcon = new Rcon($server['ip'], $server['rcon_port'], $server['rcon_password']);
                        $rcon->connect();
                        $response = $rcon->sendCommand('ListPlayers');
                        $rcon->disconnect();

                        $isOnline = true;
                        if (preg_match_all('/^\d+\.\s+.+$/m', $response, $matches)) {
                            $players = count($matches[0]);
                        }
                    } catch (Exception $e) {
                        $isOnline = false;
                    }
                ?>
                <div class="col-12 col-md-6 col-lg-4 d-flex">
                    <a href="steam://run/346110//+connect <?= $externalIP ?>:<?= $server['query_port'] ?>/" target="_blank" class="text-decoration-none text-dark flex-fill">
                        <div class="card server-card shadow-sm <?= $isOnline ? '' : 'offline' ?>">
                            <img src="<?= htmlspecialchars($server['image']) ?>" class="card-img-top server-img" alt="<?= htmlspecialchars($server['name']) ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($server['name']) ?></h5>
                                <p class="card-text mb-2">
                                    Status: <?= $isOnline ? "<span class='text-success'>Online</span> ($players player" . ($players != 1 ? 's' : '') . ")" : "<span class='text-danger'>Offline</span>" ?>
                                </p>
                                <?php if ($isOnline && $isAdmin): ?>
                                    <form method="post" action="server_control.php">
                                        <input type="hidden" name="server_ip" value="<?= $server['ip'] ?>">
                                        <input type="hidden" name="rcon_port" value="<?= $server['rcon_port'] ?>">
                                        <input type="hidden" name="rcon_password" value="<?= $server['rcon_password'] ?>">
                                        <button name="command" value="DoExit" class="btn btn-sm btn-danger me-2">
                                            <i class="fas fa-stop"></i> Stop
                                        </button>
                                        <button name="command" value="Restart" class="btn btn-sm btn-warning">
                                            <i class="fas fa-rotate-right"></i> Restart
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="tooltip-custom"><?= htmlspecialchars($server['description']) ?></div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
</div>
</body>
</html>
