<?php
require __DIR__ . '/../includes/init.php';
require_admin();

$q = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';
$st = $conn->prepare('SELECT u.id, u.full_name, u.email, u.phone, u.profile_picture, u.created_at, r.name AS role
                      FROM users u JOIN roles r ON r.id = u.role_id
                      WHERE u.full_name LIKE ? OR u.email LIKE ? ORDER BY u.id DESC');
$st->bind_param('ss', $like, $like);
$st->execute();
$users = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

$pageTitle = 'Manage Users';
require __DIR__ . '/../includes/header.php';
?>
<main class="container py-4">
  <?= flash_render() ?>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0 text-teal fw-bold">Users <span class="badge bg-teal"><?= count($users) ?></span></h1>
    <div class="d-flex gap-2">
      <form class="d-flex" method="get">
        <input class="form-control" type="search" name="q" placeholder="Search name or email" value="<?= e($q) ?>">
        <button class="btn btn-outline-teal ms-2"><i class="bi bi-search"></i></button>
      </form>
      <a href="user_form.php" class="btn btn-teal text-nowrap"><i class="bi bi-plus-lg"></i> Add user</a>
    </div>
  </div>
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>#</th><th>User</th><th>Phone</th><th>Role</th><th>Joined</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><div class="d-flex align-items-center gap-2"><?= avatar_html($r, 36) ?>
              <div><div class="fw-semibold"><?= e($r['full_name']) ?></div><div class="small text-muted"><?= e($r['email']) ?></div></div></div></td>
            <td><?= e($r['phone'] ?: '-') ?></td>
            <td><span class="badge <?= $r['role'] === 'Admin' ? 'bg-teal' : 'bg-secondary' ?>"><?= e($r['role']) ?></span></td>
            <td class="text-nowrap"><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
            <td class="text-end text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="user_form.php?id=<?= (int)$r['id'] ?>"><i class="bi bi-pencil"></i></a>
              <?php if ((int)$r['id'] !== (int)$_SESSION['user_id']): ?>
              <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= (int)$r['id'] ?>" data-name="<?= e($r['full_name']) ?>"><i class="bi bi-trash"></i></button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?><tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Delete confirmation popup -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="delete_user.php">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="delete-id">
      <div class="modal-header"><h5 class="modal-title">Delete user?</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">Are you sure you want to delete <strong id="delete-name"></strong>? This cannot be undone.</div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Yes, delete</button></div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
