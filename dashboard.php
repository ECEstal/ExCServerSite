<?php
require 'config.php';
require 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
$user = $_SESSION['user'];
$isAdmin = $_SESSION['is_admin'] ?? false;
$hasCharacter = !empty($user['character_id']);

$stmt = $pdo->prepare("SELECT activity, timestamp FROM activity_log WHERE user_id = ? ORDER BY timestamp DESC LIMIT 5");
$stmt->execute([$user['id']]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .profile-img { width: 100px; height: 100px; object-fit: cover; }
        .card { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        #chatLogBox {
            background: #1e1e1e;
            color: #dcdcdc;
            border: 1px solid #444;
            padding: 1rem;
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.875rem;
            white-space: pre-wrap;
        }
        .emoji svg {
            height: 1em !important;
            width: 1em !important;
            vertical-align: -0.15em;
        }
        #lastRefreshed {
            font-size: 0.8rem;
            color: #ccc;
        }
        /* Layout for left sidebar (profile + character) */
        .user-side-col {
            min-width: 320px;
            max-width: 360px;
        }
        .activity-narrow {
            max-width: 320px;
            min-width: 220px;
        }
        @media (max-width: 991px) {
            .user-side-col, .activity-narrow { max-width: 100%; min-width: 0; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-9 py-4 px-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <strong>Live Server Chat</strong>
                    <small id="lastRefreshed">Last Refreshed: --</small>
                </div>
                <div class="card-body p-2" id="chatLogBox">Loading chat log...</div>
                <div id="chatStatus" class="px-3 pb-2 text-muted small">
                    <span class="spinner-border spinner-border-sm text-success me-1"></span> Monitoring chat...
                </div>
                <!-- Send Message Inline -->
                <div class="card-footer">
                    <?php if ($hasCharacter): ?>
                    <form id="userNotifyForm" method="post" class="d-flex gap-2">
                        <input type="hidden" name="send_as" value="user">
                        <input type="text" class="form-control" name="notification_msg" placeholder="Type your message..." required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    <div id="notifyAlert" class="mt-2"></div>
                    <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-link me-1"></i> You must <strong>link a character on the server</strong> to use chat.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Dashboard Content: 3 columns below chat -->
            <div class="row">
                <!-- LEFT COLUMN: Profile & Character Panel -->
                <div class="col-lg-4 user-side-col mb-4">
                    <div class="card mb-4">
                        <div class="card-body d-flex align-items-center">
                            <img src="https://cdn.discordapp.com/avatars/<?= htmlspecialchars($user['id']) ?>/<?= htmlspecialchars($user['avatar']) ?>.png" class="rounded-circle profile-img me-4">
                            <div>
                                <h4><?= ucfirst(htmlspecialchars($user['username'])) ?></h4>
                                <p class="mb-0 text-muted">Email: <?= htmlspecialchars($user['email']) ?></p>
                                <p class="text-muted small">Discord ID: <?= htmlspecialchars($user['id']) ?></p>
                            </div>
                        </div>
                    </div>
<!--
                   <?php /*if ($hasCharacter): ?>
<?php 
require_once __DIR__ . '/includes/ArkProfileLite.php';
// Fetch the latest steam_id from the database if not present in session
$discordId = $user['id'];
$steamId = $user['steam_id'] ?? null;
if (!$steamId) {
    $stmt = $pdo->prepare("SELECT steam_id FROM users WHERE id = ?");
    $stmt->execute([$discordId]);
    $steamId = $stmt->fetchColumn();
}
$arkprofileDir = 'C:/users/Administrator/desktop/servers/4/serverfiles/shootergame/saved/savedarks/Ragnarok_WP/';
$profileFile = $arkprofileDir . $steamId . '.arkprofile';
echo "<!-- STEAM ID: '$steamId' -->";
echo "<!-- PROFILE FILE: $profileFile -->";
$arkProfile = ($steamId && file_exists($profileFile)) ? new ArkProfileLite($profileFile) : null;
?>
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        Character Panel
    </div>
    <div class="card-body">
        <?php if ($arkProfile && $arkProfile->playerName): ?>
            <strong>Name:</strong> <?= htmlspecialchars($arkProfile->playerName) ?><br>
            <strong>Level:</strong> <?= htmlspecialchars($arkProfile->level) ?><br>
            <strong>Tribe:</strong> <?= htmlspecialchars($arkProfile->tribeName ?: '—') ?><br>
            <strong>SteamID:</strong> <?= htmlspecialchars($arkProfile->steamId ?: '—') ?><br>
        <?php else: ?>
            <span class="text-warning">Profile not found or could not be parsed.</span>
        <?php endif; ?>
    </div>
</div>
<?php endif; */?>-->

                </div>
                <!-- MIDDLE SPACER COLUMN -->
                <div class="col-lg-4"></div>
                <!-- RIGHT COLUMN: Activity Log (narrower) -->
                <div class="col-lg-4 activity-narrow mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Activity Log</strong>
                            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="fetchActivityLog()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                        <div id="activityCollapse" class="collapse show">
                            <div class="card-body p-2">
                                <ul class="list-group list-group-flush" id="activityList">
                                    <?php if (empty($activities)): ?>
                                        <li class="list-group-item text-muted">No recent activity.</li>
                                    <?php else: ?>
                                        <?php foreach ($activities as $act): ?>
                                            <li class="list-group-item">
                                                <span><?= htmlspecialchars($act['activity']) ?></span><br>
                                                <small class="text-muted"><?= date("M d, Y H:i", strtotime($act['timestamp'])) ?></small>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END RIGHT COLUMN -->
            </div>
        </div>
    </div>
</div>

<script>
const chatLogBox = document.getElementById('chatLogBox');
const lastRefreshedEl = document.getElementById('lastRefreshed');
const chatStatus = document.getElementById('chatStatus');
const chatPing = new Audio('/assets/ping.mp3');
chatPing.volume = 0;
let lastTimestamp = 0;

function convertEmoticonsToEmoji(text) {
    const replacements = {
        ":)": "😊", ":(": "☹️", ":D": "😄", ":P": "😜", ";)": "😉",
        "<3": "❤️", ":O": "😲", ">:(": "😠", ":/": "😕", ":|": "😐",
        ":3": "😺", ":*": "😘", ":$": "😳", ":^)": "😏", ":v": "✌️",
        "xD": "😆", "XD": "😆", "B)": "😎", ":'(": "😢"
    };
    const pattern = new RegExp(Object.keys(replacements).map(k => k.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&')).join("|"), "g");
    return text.replace(pattern, match => replacements[match] || match);
}

function applyTwemoji(container) {
    if (window.twemoji) {
        twemoji.parse(container, { folder: 'svg', ext: '.svg' });
    }
}

function updateLastRefreshed() {
    const now = new Date();
    lastRefreshedEl.textContent = `Last Refreshed: ${now.toLocaleTimeString()}`;
}

function appendMessages(messages) {
    if (messages.length === 0) return false;
    for (const msg of messages) {
        const date = new Date(msg.timestamp * 1000);
        const timeStr = date.toLocaleTimeString(undefined, {
            hour: '2-digit', minute: '2-digit', second: '2-digit', timeZoneName: 'short'
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
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                const didAppend = appendMessages(data);
                lastTimestamp = data[data.length - 1].timestamp;

                if (didAppend) {
                    chatPing.play().catch(() => {});
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
        .catch(() => {
            chatStatus.innerHTML = `<i class="fas fa-exclamation-circle text-danger me-1"></i> Reconnecting...`;
            setTimeout(fetchChatLongPolling, 3000);
        });
}

function fetchActivityLog() {
    fetch('fetch_activity.php')
        .then(res => res.json())
        .then(data => {
            const activityList = document.getElementById('activityList');
            if (!activityList) return;
            if (!Array.isArray(data) || data.length === 0) {
                activityList.innerHTML = `<li class='list-group-item text-muted'>No recent activity.</li>`;
            } else {
                activityList.innerHTML = data.map(act =>
                    `<li class='list-group-item'>
                        <span>${escapeHtml(act.activity)}</span><br>
                        <small class='text-muted'>${new Date(act.timestamp).toLocaleString()}</small>
                    </li>`
                ).join('');
            }
        });
}
function escapeHtml(text) {
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Only reload if verification status just changed
let wasVerified = <?= json_encode($hasCharacter) ?>;
function pollVerificationStatus() {
    fetch('check_verification_status.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                if (data.verified && !wasVerified) {
                    location.reload();
                }
                wasVerified = data.verified;
            }
        })
        .catch(console.error);
}

setInterval(pollVerificationStatus, 30000);
setInterval(fetchActivityLog, 60000);

document.addEventListener('DOMContentLoaded', () => {
    fetchChatLongPolling();
    fetchActivityLog();

    const notifyForm = document.getElementById('userNotifyForm');
    if (notifyForm) {
        notifyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(notifyForm);
            const alertBox = document.getElementById('notifyAlert');

            fetch('send_notification.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alertBox.innerHTML = '<div class="alert alert-success">Message sent!</div>';
                    notifyForm.reset();
                } else {
                    alertBox.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to send message.'}</div>`;
                }
            })
            .catch(() => {
                alertBox.innerHTML = '<div class="alert alert-danger">An error occurred while sending.</div>';
            });
        });
    }
});
</script>
</body>
</html>
