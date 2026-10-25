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

  <!-- Welcome Hero Banner with Entrance Animation -->
  <div class="card dashboard-card dashboard-banner p-4 mb-4 animate-slide-up-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <div class="d-inline-flex align-items-center gap-2 mb-2 px-3 py-1 bg-white rounded-pill border shadow-sm small text-muted">
          <span class="status-indicator-dot"></span>
          <span>Account Active</span>
          <span class="text-muted">•</span>
          <span><?= date('l, d F Y') ?></span>
        </div>
        <h1 class="h3 fw-bold mb-1 text-dark">
          <?= e($greeting) ?>, <span class="text-teal"><?= e($u['full_name']) ?></span> <span class="hand-wave">👋</span>
        </h1>
        <p class="text-muted mb-0">Welcome back to your User Management dashboard.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="profile.php" class="btn btn-outline-teal px-3 py-2">
          <i class="bi bi-person-gear me-1"></i> Account Settings
        </a>
        <?php if (is_admin()): ?>
        <a href="admin/users.php" class="btn btn-teal px-3 py-2">
          <i class="bi bi-people me-1"></i> Manage Users
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column: User Profile Card -->
    <div class="col-md-4">
      <div class="card dashboard-card text-center h-100 animate-slide-up-2">
        <div class="card-body p-4 d-flex flex-column justify-content-center">
          <div class="mb-3">
            <div class="avatar-wrapper">
              <?= avatar_html($u, 120) ?>
            </div>
          </div>
          <h2 class="h5 fw-bold mb-1 text-dark"><?= e($u['full_name']) ?></h2>
          <div class="mb-3">
            <span class="badge bg-teal px-3 py-2 rounded-pill shadow-sm">
              <i class="bi <?= is_admin() ? 'bi-shield-check' : 'bi-person-check' ?> me-1"></i><?= e($u['role']) ?>
            </span>
          </div>
          <p class="text-muted small mb-4">
            <i class="bi bi-envelope me-1"></i> <?= e($u['email']) ?>
          </p>
          <div class="mt-auto">
            <a href="profile.php" class="btn btn-outline-teal btn-sm w-100 py-2">
              <i class="bi bi-pencil-square me-1"></i> Edit profile
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Profile Info & Admin Stats -->
    <div class="col-md-8">
      <div class="card dashboard-card mb-4 animate-slide-up-3">
        <div class="card-header bg-transparent border-bottom fw-bold text-dark py-3 px-4 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-vcard text-teal fs-5"></i>
            <span>Profile Details</span>
          </div>
          <a href="profile.php" class="btn btn-sm btn-link text-teal text-decoration-none fw-semibold p-0">
            Edit <i class="bi bi-chevron-right small"></i>
          </a>
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item info-list-item d-flex justify-content-between flex-wrap align-items-center">
            <span class="text-muted"><i class="bi bi-envelope text-teal me-2"></i>Email Address</span>
            <span class="fw-semibold text-dark"><?= e($u['email']) ?></span>
          </li>
          <li class="list-group-item info-list-item d-flex justify-content-between flex-wrap align-items-center">
            <span class="text-muted"><i class="bi bi-telephone text-teal me-2"></i>Phone Number</span>
            <span class="fw-semibold text-dark"><?= e($u['phone'] ?: '-') ?></span>
          </li>
          <li class="list-group-item info-list-item">
            <span class="text-muted d-block mb-1"><i class="bi bi-card-text text-teal me-2"></i>Bio</span>
            <div class="text-dark bg-white p-3 rounded-3 border small">
              <?= nl2br(e($u['bio'] ?: 'No bio added yet.')) ?>
            </div>
          </li>
          <li class="list-group-item info-list-item d-flex justify-content-between flex-wrap align-items-center">
            <span class="text-muted"><i class="bi bi-calendar-event text-teal me-2"></i>Member Since</span>
            <span class="fw-semibold text-dark"><?= e(date('d M Y', strtotime($u['created_at']))) ?></span>
          </li>
        </ul>
      </div>

      <?php if (is_admin()): ?>
      <div class="card admin-stat-card p-4 animate-slide-up-4">
        <div class="card-body p-0 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="stat-icon-bubble">
              <i class="bi bi-people-fill"></i>
            </div>
            <div>
              <div class="text-white-50 small text-uppercase fw-semibold letter-spacing">Total Registered Users</div>
              <div class="display-6 fw-bold text-white"><?= $total ?></div>
            </div>
          </div>
          <div>
            <a href="admin/users.php" class="btn btn-light text-teal fw-semibold px-4 py-2 shadow-sm">
              <i class="bi bi-gear-fill me-1"></i> Manage Users
            </a>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
