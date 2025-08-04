<?php $isAdmin = $_SESSION['is_admin'] ?? false; ?>
<!-- Font Awesome & Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<style>
.sidebar {
    background-color: #23272b;
    min-height: 100vh;
    color: #fff;
    padding: 2rem 1rem;
    width: 220px;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1030;
}
.sidebar h5 {
    color: #ffc107;
    letter-spacing: 2px;
}
.sidebar a {
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.7em;
    margin-bottom: 1rem;
    text-decoration: none;
    font-weight: 500;
    font-size: 1.1em;
    transition: background 0.15s;
    border-radius: 8px;
    padding: 0.5em 0.75em;
}
.sidebar a:hover,
.sidebar a.active {
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
        display: none;
    }
    #main-content {
        margin-left: 0 !important;
    }
}
@media (min-width: 992px) {
    #main-content {
        margin-left: 220px;
    }
}
</style>

<!-- DESKTOP SIDEBAR (always visible on lg+) -->
<div class="sidebar d-none d-lg-block">
  <h5><i class="fa-solid fa-gamepad fa-fw me-2"></i>ExC Dashboard</h5>
  <a href="dashboard.php"><i class="fa-solid fa-house fa-fw"></i> Home</a>
  <a href="serverstatus.php"><i class="fa-solid fa-server fa-fw"></i> Server Status</a>
  <?php if ($isAdmin): ?>
    <a href="admin.php"><i class="fa-solid fa-shield-halved fa-fw"></i> Admin Panel</a>
  <?php endif; ?>
  <a href="users.php"><i class="fa-solid fa-users fa-fw"></i> Users</a>
  <a href="logout.php"><i class="fa-solid fa-right-from-bracket fa-fw"></i> Logout</a>
  <hr>
  <p class="text-muted small ms-2">&copy; <?= date('Y') ?> Existential Crisis</p>
</div>

<!-- MOBILE OFFCANVAS (shows ONLY on mobile/tablet) -->
<nav class="navbar navbar-dark bg-dark d-lg-none">
  <div class="container-fluid">
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-label="Open sidebar">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasSidebarLabel"><i class="fa-solid fa-gamepad fa-fw me-2"></i>ExC Dashboard</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body px-0">
    <a href="dashboard.php"><i class="fa-solid fa-house fa-fw"></i> Home</a>
    <a href="serverstatus.php"><i class="fa-solid fa-server fa-fw"></i> Server Status</a>
    <?php if ($isAdmin): ?>
      <a href="admin.php"><i class="fa-solid fa-shield-halved fa-fw"></i> Admin Panel</a>
    <?php endif; ?>
    <a href="users.php"><i class="fa-solid fa-users fa-fw"></i> Users</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket fa-fw"></i> Logout</a>
    <hr>
    <p class="text-muted small ms-2">&copy; <?= date('Y') ?> Existential Crisis</p>
  </div>
</div>
<!-- Bootstrap JS for Offcanvas -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
