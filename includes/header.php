<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'User Management') ?> | User Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="<?= is_logged_in() ? '' : 'auth-bg' ?>">
<?php if (is_logged_in()): ?>
<nav class="navbar navbar-expand-md navbar-dark bg-teal sticky-top">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/dashboard.php"><i class="bi bi-people-fill me-1"></i> UserMS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/profile.php">Edit Profile</a></li>
        <?php if (is_admin()): ?>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/users.php">Manage Users</a></li>
        <?php endif; ?>
      </ul>
      <span class="navbar-text me-md-3 small"><?= e($_SESSION['name']) ?> <span class="badge bg-light text-dark"><?= e($_SESSION['role']) ?></span></span>
      <form method="post" action="<?= BASE_URL ?>/logout.php" class="d-inline"><?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-light mt-2 mt-md-0"><i class="bi bi-box-arrow-right"></i> Logout</button>
      </form>
    </div>
  </div>
</nav>
<?php endif; ?>
