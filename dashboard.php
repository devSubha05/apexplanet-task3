<?php
require __DIR__ . '/includes/init.php';
require_login();
$u = get_user($conn, (int)$_SESSION['user_id']);
if (!$u) { session_destroy(); redirect('login.php'); }          // account deleted while logged in

$total = is_admin() ? (int)$conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0] : null;
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
  <?= flash_render() ?>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body p-4">
          <div class="mb-3"><?= avatar_html($u, 120) ?></div>
          <h2 class="h5 mb-1"><?= e($u['full_name']) ?></h2>
          <span class="badge bg-teal"><?= e($u['role']) ?></span>
          <div class="mt-3"><a href="profile.php" class="btn btn-outline-teal btn-sm"><i class="bi bi-pencil-square"></i> Edit profile</a></div>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Profile information</div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between flex-wrap"><span class="text-muted">Email</span><span><?= e($u['email']) ?></span></li>
          <li class="list-group-item d-flex justify-content-between flex-wrap"><span class="text-muted">Phone</span><span><?= e($u['phone'] ?: '-') ?></span></li>
          <li class="list-group-item"><span class="text-muted d-block">Bio</span><?= nl2br(e($u['bio'] ?: 'No bio added yet.')) ?></li>
          <li class="list-group-item d-flex justify-content-between flex-wrap"><span class="text-muted">Member since</span><span><?= e(date('d M Y', strtotime($u['created_at']))) ?></span></li>
        </ul>
      </div>
      <?php if (is_admin()): ?>
      <div class="card shadow-sm border-teal">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div><div class="text-muted small">Total registered users</div><div class="display-6 text-teal fw-bold"><?= $total ?></div></div>
          <a href="admin/users.php" class="btn btn-teal">Manage users</a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
