<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}

// Default values
$breedingRate = '';
$tamingRate = '';
$gatheringRate = '';
$xpMultiplier = '';
$babyCuddle = '';
$eggHatch = '';
$layEgg = '';
$matingInterval = '';

// Load current DynamicConfig.ini if exists
$configPath = __DIR__ . '/DynamicConfig.ini';
if (file_exists($configPath)) {
    $rawData = file_get_contents($configPath);
    if (preg_match('/BabyMatureSpeedMultiplier=(.+)/', $rawData, $match)) {
        $breedingRate = trim($match[1]);
    }
    if (preg_match('/TamingSpeedMultiplier=(.+)/', $rawData, $match)) {
        $tamingRate = trim($match[1]);
    }
    if (preg_match('/HarvestAmountMultiplier=(.+)/', $rawData, $match)) {
        $gatheringRate = trim($match[1]);
    }
    if (preg_match('/XPMultiplier=(.+)/', $rawData, $match)) {
        $xpMultiplier = trim($match[1]);
    }
    if (preg_match('/BabyCuddleIntervalMultiplier=(.+)/', $rawData, $match)) {
        $babyCuddle = trim($match[1]);
    }
    if (preg_match('/EggHatchSpeedMultiplier=(.+)/', $rawData, $match)) {
        $eggHatch = trim($match[1]);
    }
    if (preg_match('/LayEggIntervalMultiplier=(.+)/', $rawData, $match)) {
        $layEgg = trim($match[1]);
    }
    if (preg_match('/MatingIntervalMultiplier=(.+)/', $rawData, $match)) {
        $matingInterval = trim($match[1]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
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
        <!-- Main content: fills remaining space (100% on mobile) -->
            <h1 class="mb-4">Admin Panel</h1>
            <p class="user-welcome">Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?>. You have admin access.</p>

            <!-- Server Control -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header"><strong>Server Control</strong></div>
                <div class="card-body">
                    <button class="btn btn-danger btn-icon me-3" onclick="alert('Stop server functionality coming soon')">
                        <i class="fas fa-power-off"></i> Stop Server
                    </button>
                    <button class="btn btn-success btn-icon" onclick="alert('Restart server functionality coming soon')">
                        <i class="fas fa-redo"></i> Restart Server
                    </button>
                </div>
            </div>

            <!-- Rates & Multipliers Settings -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header"><strong>Rates & Multipliers Settings</strong></div>
                <div class="card-body">
                    <form id="ratesForm" method="post">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="breedingRate" class="form-label">Baby Maturation</label>
                                <input type="number" step="any" min="0" class="form-control" name="breeding_rate" value="<?= htmlspecialchars($breedingRate) ?>" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tamingRate" class="form-label">Taming Rate</label>
                                <input type="number" step="any" min="0" class="form-control" name="taming_rate" value="<?= htmlspecialchars($tamingRate) ?>" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="gatheringRate" class="form-label">Gathering Rate</label>
                                <input type="number" step="any" min="0" class="form-control" name="gathering_rate" value="<?= htmlspecialchars($gatheringRate) ?>" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="xpMultiplier" class="form-label">XP Multiplier</label>
                                <input type="number" step="any" min="0" class="form-control" name="xp_multiplier" value="<?= htmlspecialchars($xpMultiplier) ?>" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="babyCuddle" class="form-label">Baby Cuddle Interval</label>
                                <input type="number" step="any" min="0" class="form-control" name="baby_cuddle_interval_multiplier" value="<?= htmlspecialchars($babyCuddle) ?>" readonly />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="eggHatch" class="form-label">Egg Hatch Speed</label>
                                <input type="number" step="any" min="0" class="form-control" name="egg_hatch_speed_multiplier" value="<?= htmlspecialchars($eggHatch) ?>" readonly />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="layEgg" class="form-label">Lay Egg Interval</label>
                                <input type="number" step="any" min="0" class="form-control" name="lay_egg_interval_multiplier" value="<?= htmlspecialchars($layEgg) ?>" readonly />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="matingInterval" class="form-label">Mating Interval</label>
                                <input type="number" step="any" min="0" class="form-control" name="mating_interval_multiplier" value="<?= htmlspecialchars($matingInterval) ?>" readonly />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-icon">
                            <i class="fas fa-floppy-disk"></i> Save Rates
                        </button>
                        <div id="ratesAlert" class="mt-3"></div>
                    </form>
                </div>
            </div>

            <!-- Send Notification -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header"><strong>Send Server Notification</strong></div>
                <div class="card-body">

                    <!-- Live Chat Log -->
                    <div id="chatLogBox">Loading chat log...</div>
                    <div id="lastRefreshed">Last Refreshed: --</div>
                    <div id="chatStatus" class="mt-2 text-muted">
                        <span class="spinner-border spinner-border-sm text-success me-1" role="status" aria-hidden="true"></span>
                        Monitoring chat...
                    </div>

                    <form id="notifyForm" method="post" action="send_notification.php" class="mt-3">
                        <div class="mb-3">
                            <label for="notificationMsg" class="form-label">Notification Message</label>
                            <input type="text" class="form-control" id="notificationMsg" name="notification_msg" autocomplete="off" placeholder="Enter your message here..." required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Send As</label><br />
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="send_as" id="sendAsUser" value="user" checked>
                                <label class="form-check-label" for="sendAsUser">User</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="send_as" id="sendAsSystem" value="system">
                                <label class="form-check-label" for="sendAsSystem">System</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-icon">
                            <i class="fas fa-bullhorn"></i> Send Notification
                        </button>
                    </form>
                    <div id="notifyAlert" class="mt-3"></div>
                </div>
            </div>

            <a href="dashboard.php" class="btn btn-secondary btn-icon">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
<script src="https://cdn.jsdelivr.net/npm/twemoji@14.0.2/dist/twemoji.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/twemoji@14.0.2/dist/twemoji.min.js"></script>
<script>
const chatPing = new Audio('/assets/ping.mp3');
chatPing.volume = 1;

function convertEmoticonsToEmoji(text) {
    const replacements = {
        "=)": "😊",
        ":)": "😊",
        ":-)": "😊",
        ":-(": "☹️",
        ":(": "☹️",
        ":D": "😄",
        ":-D": "😄",
        "=D": "😄",
        ":P": "😜",
        ":-P": "😜",
        ";)": "😉",
        ";-)": "😉",
        "<3": "❤️",
        ":'(": "😢",
        ":O": "😲",
        ":-O": "😲",
        ">:(": "😠",
        ":/": "😕",
        ":-/": "😕",
        ":|": "😐",
        ":-|": "😐",
        ":3": "😺",
        ":-*": "😘",
        ":$": "😳",
        ":^)": "😏",
        ":v": "✌️",
        "D:": "😧",
        "DX": "😫",
        "xD": "😆",
        "XD": "😆",
        "B)": "😎"
    };

    const pattern = new RegExp(
        Object.keys(replacements)
            .map(k => k.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&'))
            .join("|"),
        "g"
    );

    return text.replace(pattern, match => replacements[match] || match);
}

function applyTwemoji(container) {
    if (window.twemoji) {
        twemoji.parse(container, {
            folder: 'svg',
            ext: '.svg'
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // ===== Live Chat Polling =====
    const chatLogBox = document.getElementById('chatLogBox');
    const lastRefreshedEl = document.getElementById('lastRefreshed');
    const chatStatus = document.getElementById('chatStatus');
    let lastTimestamp = 0;

    function updateLastRefreshed() {
        const now = new Date();
        lastRefreshedEl.textContent = `Last Refreshed: ${now.toLocaleTimeString()}`;
    }

    function appendMessages(messages) {
        if (messages.length === 0) return false;

        for (const msg of messages) {
            const date = new Date(msg.timestamp * 1000);
            const timeStr = date.toLocaleTimeString(undefined, {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZoneName: 'short'
            });

            const div = document.createElement('div');
            const converted = convertEmoticonsToEmoji(msg.message);
            div.innerHTML = `[${timeStr}] <strong class="text-primary">${msg.player_name}</strong>: ${converted}`;
            chatLogBox.appendChild(div);
            applyTwemoji(div);
        }

        chatLogBox.scrollTop = chatLogBox.scrollHeight;
        updateLastRefreshed();
        return true;
    }

    function fetchChatLongPolling() {
        chatStatus.innerHTML = `<span class="spinner-border spinner-border-sm text-success me-1" role="status"></span> Monitoring chat...`;

        fetch(`fetch_chat.php?since=${lastTimestamp}`)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (Array.isArray(data) && data.length > 0) {
                    const didAppend = appendMessages(data);
                    lastTimestamp = data[data.length - 1].timestamp;

                    if (didAppend) {
                        chatPing.play().catch(err => {
                            // Optional: suppress sound errors
                        });

                        chatStatus.classList.add('text-success', 'fw-bold');
                        chatStatus.innerHTML = `<i class="fas fa-bolt me-1"></i> New chat received`;
                        setTimeout(() => {
                            chatStatus.innerHTML = `<span class="spinner-border spinner-border-sm text-success me-1"></span> Monitoring chat...`;
                            chatStatus.classList.remove('fw-bold');
                        }, 2000);
                    }
                }

                fetchChatLongPolling();
            })
            .catch(err => {
                chatStatus.innerHTML = `<i class="fas fa-exclamation-circle text-danger me-1"></i> Reconnecting...`;
                setTimeout(fetchChatLongPolling, 3000);
            });
    }

    fetchChatLongPolling();

    // ===== AJAX Notification Submission =====
    const notifyForm = document.getElementById('notifyForm');
    const notifyAlert = document.getElementById('notifyAlert');

    notifyForm.addEventListener('submit', function (e) {
        e.preventDefault();

        notifyAlert.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Sending...`;

        const formData = new FormData(this);

        fetch('send_notification.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.text())
        .then(text => {
            if (text.trim() === 'OK' || text.trim().toLowerCase().includes('success')) {
                notifyAlert.innerHTML = `<div class="alert alert-success p-2 mb-2">Notification sent!</div>`;
                notifyForm.reset();
                setTimeout(() => notifyAlert.innerHTML = '', 1800);
            } else {
                notifyAlert.innerHTML = `<div class="alert alert-danger p-2 mb-2">${text}</div>`;
            }
        })
        .catch(err => {
            notifyAlert.innerHTML = `<div class="alert alert-danger p-2 mb-2">Error: ${err.message}</div>`;
        });
    });
});
</script>

</body>
</html>
