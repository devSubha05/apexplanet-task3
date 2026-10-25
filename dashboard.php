<?php
require __DIR__ . '/includes/init.php';
require_login();
$u = get_user($conn, (int)$_SESSION['user_id']);
if (!$u) { session_destroy(); redirect('login.php'); }     

$total = is_admin() ? (int)$conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0] : null;
$pageTitle = 'Dashboard';
$bodyClass = 'dashboard-bg';

// Dynamic greeting based on current hour
$hour = (int)date('G');
if ($hour >= 5 && $hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}

require __DIR__ . '/includes/header.php';
?>
<main class="container py-4 dashboard-main">
  <?= flash_render() ?>

  <!-- Box 1: Welcome Hero Banner (Rainbow / Multicolor Iridescent Box) -->
  <div class="card dashboard-card box-multicolor p-4 mb-4 animate-slide-up-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <div class="d-inline-flex align-items-center gap-2 mb-2 px-3 py-1 rounded-pill border small" style="background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.12) !important;">
          <span class="status-indicator-dot"></span>
          <span class="text-light fw-medium">Portal Active</span>
          <span class="text-secondary">•</span>
          <span class="text-info"><?= date('l, d F Y') ?></span>
        </div>
        <h1 class="h3 fw-bold mb-1 text-white">
          <?= e($greeting) ?>, <span class="brand-gradient"><?= e($u['full_name']) ?></span> <span class="hand-wave">👋</span>
        </h1>
        <p class="text-secondary mb-0 small">Welcome to your high-performance User Management command center.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="profile.php" class="btn btn-outline-teal px-3 py-2">
          <i class="bi bi-person-gear me-1"></i> Account Settings
        </a>
        <?php if (is_admin()): ?>
        <a href="admin/users.php" class="btn btn-purple px-3 py-2">
          <i class="bi bi-people-fill me-1"></i> Manage Users
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Box 2: Left Column Profile Card (Teal & Emerald Box) -->
    <div class="col-md-4">
      <div class="card dashboard-card box-teal text-center h-100 animate-slide-up-2">
        <div class="card-body p-4 d-flex flex-column justify-content-center">
          <div class="mb-3">
            <div class="avatar-wrapper">
              <?= avatar_html($u, 120) ?>
            </div>
          </div>
          <h2 class="h5 fw-bold mb-1 text-white"><?= e($u['full_name']) ?></h2>
          <div class="mb-3">
            <span class="badge <?= is_admin() ? 'badge-gradient-purple' : 'badge-gradient-teal' ?> px-3 py-2 rounded-pill">
              <i class="bi <?= is_admin() ? 'bi-shield-check' : 'bi-person-check' ?> me-1"></i><?= e($u['role']) ?>
            </span>
          </div>
          <p class="text-secondary small mb-4">
            <i class="bi bi-envelope text-info me-1"></i> <?= e($u['email']) ?>
          </p>
          <div class="mt-auto">
            <a href="profile.php" class="btn btn-teal btn-sm w-100 py-2">
              <i class="bi bi-pencil-square me-1"></i> Edit profile
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Box 3 & 4: Right Column Details & Admin Stats (Purple & Multi-color Boxes) -->
    <div class="col-md-8">
      <!-- Box 3: Profile Information Card (Electric Purple & Indigo Box) -->
      <div class="card dashboard-card box-purple mb-4 animate-slide-up-3">
        <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 fw-bold text-white py-3 px-4 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-vcard text-info fs-5"></i>
            <span>Profile Details</span>
          </div>
          <a href="profile.php" class="btn btn-sm btn-link text-info text-decoration-none fw-semibold p-0">
            Edit Details <i class="bi bi-chevron-right small"></i>
          </a>
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item info-list-item d-flex justify-content-between flex-wrap align-items-center">
            <div class="d-flex align-items-center gap-3">
              <span class="icon-box-cyan"><i class="bi bi-envelope"></i></span>
              <span class="text-secondary">Email Address</span>
            </div>
            <span class="fw-semibold text-white"><?= e($u['email']) ?></span>
          </li>
          <li class="list-group-item info-list-item d-flex justify-content-between flex-wrap align-items-center">
            <div class="d-flex align-items-center gap-3">
              <span class="icon-box-emerald"><i class="bi bi-telephone"></i></span>
              <span class="text-secondary">Phone Number</span>
            </div>
            <span class="fw-semibold text-white"><?= e($u['phone'] ?: '-') ?></span>
          </li>
          <li class="list-group-item info-list-item">
            <div class="d-flex align-items-center gap-3 mb-2">
              <span class="icon-box-amber"><i class="bi bi-card-text"></i></span>
              <span class="text-secondary">Bio / About Me</span>
            </div>
            <div class="text-light p-3 rounded-3 border small" style="background: rgba(15, 23, 42, 0.6); border-color: rgba(255, 255, 255, 0.08) !important;">
              <?= nl2br(e($u['bio'] ?: 'No bio added yet.')) ?>
            </div>
          </li>
          <li class="list-group-item info-list-item d-flex justify-content-between flex-wrap align-items-center">
            <div class="d-flex align-items-center gap-3">
              <span class="icon-box-rose"><i class="bi bi-calendar-event"></i></span>
              <span class="text-secondary">Member Since</span>
            </div>
            <span class="fw-semibold text-white"><?= e(date('d M Y', strtotime($u['created_at']))) ?></span>
          </li>
        </ul>
      </div>

      <?php if (is_admin()): ?>
      <!-- Box 4: Admin Metric Box (Sunset Amber & Warm Orange Box) -->
      <div class="card stat-card-gradient stat-gradient-amber p-4 animate-slide-up-4">
        <div class="card-body p-0 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="stat-icon-bubble">
              <i class="bi bi-people-fill"></i>
            </div>
            <div>
              <div class="text-white-50 small text-uppercase fw-bold" style="letter-spacing: 0.06em;">Total Registered Users</div>
              <div class="display-6 fw-bold text-white"><?= $total ?></div>
            </div>
          </div>
          <div>
            <a href="admin/users.php" class="btn btn-light text-dark fw-bold px-4 py-2 shadow">
              <i class="bi bi-shield-lock me-1"></i> Manage Users
            </a>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
