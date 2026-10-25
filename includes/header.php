<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'User Management') ?> | User Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="<?= !empty($bodyClass) ? e($bodyClass) : (is_logged_in() ? 'dashboard-bg' : 'auth-bg') ?>">
<?php if (is_logged_in()): ?>
<nav class="navbar navbar-expand-md navbar-dark portal-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold brand-gradient" href="<?= BASE_URL ?>/dashboard.php">
      <i class="bi bi-people-fill me-1"></i> UserMS
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= str_ends_with($_SERVER['SCRIPT_NAME'] ?? '', 'dashboard.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= str_ends_with($_SERVER['SCRIPT_NAME'] ?? '', 'profile.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/profile.php"><i class="bi bi-person-gear me-1"></i> Edit Profile</a></li>
        <?php if (is_admin()): ?>
        <li class="nav-item"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'admin/') ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users.php"><i class="bi bi-shield-lock me-1"></i> Manage Users</a></li>
        <?php endif; ?>
      </ul>
      <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
        <span class="navbar-text small text-light me-md-2">
          <i class="bi bi-person-circle text-info me-1"></i><?= e($_SESSION['name']) ?> 
          <span class="badge <?= is_admin() ? 'badge-gradient-purple' : 'badge-gradient-teal' ?> ms-1"><?= e($_SESSION['role']) ?></span>
        </span>
        <form method="post" action="<?= BASE_URL ?>/logout.php" class="d-inline mb-0"><?= csrf_field() ?>
          <button class="btn btn-sm btn-outline-danger px-3"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
        </form>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>
