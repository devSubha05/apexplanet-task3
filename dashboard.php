<?php
require __DIR__ . '/includes/init.php';
require_login();
$u = get_user($conn, (int)$_SESSION['user_id']);
if (!$u) { session_destroy(); redirect('login.php'); }     

$isAdmin = is_admin();
$total = $isAdmin ? (int)$conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0] : null;
$totalAdmins = $isAdmin ? (int)$conn->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'Admin'")->fetch_row()[0] : null;
$totalUsers = $isAdmin ? max(0, $total - $totalAdmins) : null;

$pageTitle = $isAdmin ? 'Admin Dashboard' : 'Dashboard';
$bodyClass = 'dashboard-bg';

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

  <div class="card dashboard-card box-multicolor p-4 mb-4 animate-slide-up-1">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
      <div>
        <div class="d-inline-flex align-items-center gap-2 mb-2 px-3 py-1 rounded-pill border small" style="background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.12) !important;">
          <span class="status-indicator-dot"></span>
          <span class="text-light fw-medium"><?= $isAdmin ? 'Administrator Mode' : 'Portal Active' ?></span>
          <span class="text-secondary">•</span>
          <span class="text-info"><?= date('l, d F Y') ?></span>
        </div>
        <h1 class="h3 fw-bold mb-1 text-white">
          <?= e($greeting) ?>, <span class="brand-gradient"><?= e($u['full_name']) ?></span> <span class="hand-wave">👋</span>
        </h1>
        <p class="text-secondary mb-0 small">
          <?= $isAdmin 
              ? 'Welcome to the Administrator Command Center. Monitor system metrics and manage user privileges.' 
              : 'Welcome back to your User Management dashboard.' ?>
        </p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <?php if ($isAdmin): ?>
        <a href="admin/users.php" class="btn btn-purple px-3 py-2 shadow">
          <i class="bi bi-people-fill me-1"></i> Manage Users
        </a>
        <a href="admin/user_form.php" class="btn btn-teal px-3 py-2 shadow">
          <i class="bi bi-person-plus-fill me-1"></i> Add User
        </a>
        <?php endif; ?>
        <a href="profile.php" class="btn btn-outline-teal px-3 py-2">
          <i class="bi bi-person-gear me-1"></i> Account Settings
        </a>
      </div>
    </div>
  </div>

  <div class="row g-4">
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
            <span class="badge <?= $isAdmin ? 'badge-gradient-purple' : 'badge-gradient-teal' ?> px-3 py-2 rounded-pill">
              <i class="bi <?= $isAdmin ? 'bi-shield-check' : 'bi-person-check' ?> me-1"></i><?= e($u['role']) ?>
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

    <div class="col-md-8">
      <?php if ($isAdmin): ?>
      <div class="row g-3 mb-4 animate-slide-up-3">
        <div class="col-sm-6 col-lg-4">
          <div class="card stat-card-gradient stat-gradient-amber p-3 h-100">
            <div class="d-flex align-items-center gap-3">
              <div class="stat-icon-bubble" style="width:48px; height:48px; font-size:1.4rem;">
                <i class="bi bi-people-fill"></i>
              </div>
              <div>
                <div class="text-white-50 small text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing:0.04em;">Total Users</div>
                <div class="h3 fw-bold text-white mb-0"><?= $total ?></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="card stat-card-gradient stat-gradient-purple p-3 h-100">
            <div class="d-flex align-items-center gap-3">
              <div class="stat-icon-bubble" style="width:48px; height:48px; font-size:1.4rem;">
                <i class="bi bi-shield-check"></i>
              </div>
              <div>
                <div class="text-white-50 small text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing:0.04em;">Admins</div>
                <div class="h3 fw-bold text-white mb-0"><?= $totalAdmins ?></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-12 col-lg-4">
          <div class="card stat-card-gradient stat-gradient-cyan p-3 h-100">
            <div class="d-flex align-items-center gap-3">
              <div class="stat-icon-bubble" style="width:48px; height:48px; font-size:1.4rem;">
                <i class="bi bi-person-check"></i>
              </div>
              <div>
                <div class="text-white-50 small text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing:0.04em;">Members</div>
                <div class="h3 fw-bold text-white mb-0"><?= $totalUsers ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

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
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
