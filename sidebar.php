<?php
$isAdmin = $_SESSION['is_admin'] ?? false;

// Sidebar nav links config (edit in one place!)
$navLinks = [
    [
        'href' => 'dashboard.php',
        'icon' => 'fa-house',
        'label' => 'Home'
    ],
    [
        'href' => 'serverstatus.php',
        'icon' => 'fa-server',
        'label' => 'Server Status'
    ],
    $isAdmin ? [
        'href' => 'admin.php',
        'icon' => 'fa-shield-halved',
        'label' => 'Admin Panel'
    ] : null,
    [
        'href' => 'users.php',
        'icon' => 'fa-users',
        'label' => 'Users'
    ],
    [
        'href' => 'logout.php',
        'icon' => 'fa-right-from-bracket',
        'label' => 'Logout'
    ],
];
$navLinks = array_filter($navLinks); // Remove nulls
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<style>
.sidebar {
    background-color: #23272b;
    min-height: 100vh;
    color: #fff;
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.sidebar h5 {
    color: #ffc107;
    letter-spacing: 2px;
}
.sidebar .nav-link {
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.7em;
    margin-bottom: 1rem;
    font-weight: 500;
    font-size: 1.1em;
    transition: background 0.15s;
    border-radius: 8px;
    padding: 0.5em 0.75em;
}
.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background: #343a40;
    color: #ffc107;
    text-decoration: none;
}
.sidebar .fa-fw {
    min-width: 22px;
    text-align: center;
}
.sidebar hr {
    border-color: #444;
}
@media (max-width: 991px) {
    .sidebar {
        min-height: 0;
        padding: 1.5rem 1rem;
    }
}
</style>

<!-- Mobile navbar -->
<nav class="navbar navbar-dark bg-dark d-lg-none">
  <div class="container-fluid">
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-label="Open sidebar">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<!-- Offcanvas Sidebar (Mobile) -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasSidebarLabel"><i class="fa-solid fa-gamepad fa-fw me-2"></i>ExC Dashboard</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body px-0">
    <nav class="nav flex-column">
      <?php foreach ($navLinks as $nav): ?>
        <a class="nav-link<?= (basename($_SERVER['PHP_SELF']) == $nav['href']) ? ' active' : '' ?>" href="<?= $nav['href'] ?>">
          <i class="fa-solid <?= $nav['icon'] ?> fa-fw"></i> <?= htmlspecialchars($nav['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <hr>
    <p class="text-muted small ms-2 mb-0">&copy; <?= date('Y') ?> Existential Crisis</p>
  </div>
</div>

<!-- Desktop Sidebar -->
<div class="col-lg-3 d-none d-lg-flex sidebar flex-column justify-content-between">
  <div>
    <h5><i class="fa-solid fa-gamepad fa-fw me-2"></i>ExC Dashboard</h5>
    <nav class="nav flex-column">
      <?php foreach ($navLinks as $nav): ?>
        <a class="nav-link<?= (basename($_SERVER['PHP_SELF']) == $nav['href']) ? ' active' : '' ?>" href="<?= $nav['href'] ?>">
          <i class="fa-solid <?= $nav['icon'] ?> fa-fw"></i> <?= htmlspecialchars($nav['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>
  <div>
    <hr>
    <p class="text-muted small ms-2 mb-0">&copy; <?= date('Y') ?> Existential Crisis</p>
  </div>
</div>

<!-- Bootstrap JS for Offcanvas -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
