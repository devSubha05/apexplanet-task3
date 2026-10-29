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
$bodyClass = 'dashboard-bg';
require __DIR__ . '/../includes/header.php';
?>
<main class="container py-4 dashboard-main">
  <?= flash_render() ?>

  <div class="card dashboard-card box-purple p-4 mb-4 animate-slide-up-1">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-info"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active text-light" aria-current="page">Users</li>
          </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
          <h1 class="h3 fw-bold mb-0 text-white">User Management</h1>
          <span class="badge badge-gradient-purple rounded-pill px-3 py-1"><?= count($users) ?> Total</span>
        </div>
        <p class="text-secondary small mb-0 mt-1">Search, monitor, create, and manage registered system accounts.</p>
      </div>

      <div class="d-flex flex-wrap align-items-center gap-2">
        <form class="d-flex" method="get">
          <div class="input-group">
            <input class="form-control" type="search" name="q" placeholder="Search name or email..." value="<?= e($q) ?>" aria-label="Search">
            <button class="btn btn-outline-teal" type="submit"><i class="bi bi-search"></i></button>
            <?php if ($q !== ''): ?>
            <a href="users.php" class="btn btn-outline-secondary" title="Clear search"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
          </div>
        </form>
        <a href="user_form.php" class="btn btn-purple text-nowrap px-3 shadow">
          <i class="bi bi-person-plus-fill me-1"></i> Add User
        </a>
      </div>
    </div>
  </div>

  <div class="card dashboard-card box-cyan overflow-hidden mb-4 animate-slide-up-2">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th class="ps-4 text-uppercase small fw-bold" style="letter-spacing:0.04em;">#</th>
            <th class="text-uppercase small fw-bold" style="letter-spacing:0.04em;">User</th>
            <th class="text-uppercase small fw-bold" style="letter-spacing:0.04em;">Phone</th>
            <th class="text-uppercase small fw-bold" style="letter-spacing:0.04em;">Role</th>
            <th class="text-uppercase small fw-bold" style="letter-spacing:0.04em;">Joined Date</th>
            <th class="text-end pe-4 text-uppercase small fw-bold" style="letter-spacing:0.04em;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $idx => $r): ?>
          <tr>
            <td class="ps-4 text-secondary fw-semibold"><?= $idx + 1 ?></td>
            <td>
              <div class="d-flex align-items-center gap-3">
                <div class="avatar-wrapper">
                  <?= avatar_html($r, 42) ?>
                </div>
                <div>
                  <div class="fw-bold text-white mb-0"><?= e($r['full_name']) ?></div>
                  <div class="small text-secondary"><i class="bi bi-envelope me-1 text-info"></i><?= e($r['email']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <?php if ($r['phone']): ?>
                <span class="text-light small"><i class="bi bi-telephone text-success me-1"></i><?= e($r['phone']) ?></span>
              <?php else: ?>
                <span class="text-secondary small">-</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($r['role'] === 'Admin'): ?>
                <span class="badge badge-gradient-purple rounded-pill px-3 py-1">
                  <i class="bi bi-shield-check me-1"></i>Admin
                </span>
              <?php else: ?>
                <span class="badge badge-gradient-teal rounded-pill px-3 py-1">
                  <i class="bi bi-person me-1"></i>User
                </span>
              <?php endif; ?>
            </td>
            <td class="text-nowrap small text-secondary">
              <i class="bi bi-calendar3 me-1 text-info"></i><?= e(date('d M Y', strtotime($r['created_at']))) ?>
            </td>
            <td class="text-end pe-4 text-nowrap">
              <a class="btn btn-sm btn-outline-teal me-1" href="user_form.php?id=<?= (int)$r['id'] ?>" title="Edit User">
                <i class="bi bi-pencil-square"></i>
              </a>
              <?php if ((int)$r['id'] !== (int)$_SESSION['user_id']): ?>
              <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= (int)$r['id'] ?>" data-name="<?= e($r['full_name']) ?>" title="Delete User">
                <i class="bi bi-trash"></i>
              </button>
              <?php else: ?>
              <span class="badge border border-secondary text-secondary small">Current</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-5">
              <div class="py-4">
                <i class="bi bi-people text-secondary opacity-50 display-4 d-block mb-3"></i>
                <h6 class="fw-semibold text-white mb-1">No users found</h6>
                <p class="small text-secondary mb-0">Try adjusting your search criteria or add a new user.</p>
              </div>
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content border-0 shadow-lg box-rose" method="post" action="delete_user.php" style="border-radius:1.25rem; overflow:hidden;">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="delete-id">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Delete User Account</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <p class="mb-2 text-white">Are you sure you want to permanently delete <strong id="delete-name" class="text-warning"></strong>?</p>
        <p class="text-secondary small mb-0"><i class="bi bi-info-circle me-1 text-danger"></i>This action cannot be undone and will permanently remove this user account from the system.</p>
      </div>
      <div class="modal-footer border-top-0 px-4 py-3" style="background: rgba(0, 0, 0, 0.2);">
        <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger px-4 shadow"><i class="bi bi-trash me-1"></i>Yes, Delete</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
