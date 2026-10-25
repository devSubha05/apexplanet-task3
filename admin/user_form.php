<?php
require __DIR__ . '/../includes/init.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$editing = $id > 0;
$u = ['full_name' => '', 'email' => '', 'phone' => '', 'role' => 'User'];
if ($editing) {
    $u = get_user($conn, $id);
    if (!$u) { flash('danger', 'User not found.'); redirect('admin/users.php'); }
}
$roles = $conn->query('SELECT name FROM roles ORDER BY id')->fetch_all(MYSQLI_ASSOC);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name  = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $role  = $_POST['role'] ?? '';
    $pass  = $_POST['password'] ?? '';

    if ($m = v_name($name))   $errors['full_name'] = $m;
    if ($m = v_email($email)) $errors['email'] = $m;
    elseif (email_taken($conn, $email, $id)) $errors['email'] = 'This email is already in use.';
    if ($m = v_phone($phone)) $errors['phone'] = $m;
    $rid = role_id($conn, $role);
    if (!$rid) $errors['role'] = 'Choose a valid role.';
    if ($editing && $id === (int)$_SESSION['user_id'] && $role !== 'Admin') $errors['role'] = 'You cannot remove your own admin role.';
    if (!$editing || $pass !== '') { if ($m = v_password($pass)) $errors['password'] = $m; }

    if (!$errors) {
        $phoneDb = $phone !== '' ? $phone : null;
        if ($editing) {
            if ($pass !== '') {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $st = $conn->prepare('UPDATE users SET full_name=?, email=?, phone=?, role_id=?, password_hash=? WHERE id=?');
                $st->bind_param('sssisi', $name, $email, $phoneDb, $rid, $hash, $id);
            } else {
                $st = $conn->prepare('UPDATE users SET full_name=?, email=?, phone=?, role_id=? WHERE id=?');
                $st->bind_param('sssii', $name, $email, $phoneDb, $rid, $id);
            }
            $st->execute();
            if ($id === (int)$_SESSION['user_id']) $_SESSION['name'] = $name;
            flash('success', 'User updated successfully.');
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $st = $conn->prepare('INSERT INTO users (role_id, full_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)');
            $st->bind_param('issss', $rid, $name, $email, $phoneDb, $hash);
            $st->execute();
            flash('success', 'User added successfully.');
        }
        redirect('admin/users.php');
    }
    $u = ['full_name' => $name, 'email' => $email, 'phone' => $phone, 'role' => $role];
}
$pageTitle = $editing ? 'Edit User' : 'Add User';
$bodyClass = 'dashboard-bg';
require __DIR__ . '/../includes/header.php';
?>
<main class="container py-4 dashboard-main" style="max-width:680px">
  <?= flash_render() ?>

  <!-- Box 1: Header Banner (Teal & Emerald Box) -->
  <div class="card dashboard-card box-teal p-4 mb-4 animate-slide-up-1">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-info"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="users.php" class="text-info">Users</a></li>
            <li class="breadcrumb-item active text-light" aria-current="page"><?= $editing ? 'Edit' : 'Add' ?></li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-1 text-white"><?= $editing ? 'Update User Account' : 'Create New User' ?></h1>
        <p class="text-secondary small mb-0"><?= $editing ? 'Modify user credentials, contact details, and role permissions.' : 'Register a new user directly into the system with custom role.' ?></p>
      </div>
      <div>
        <a href="users.php" class="btn btn-outline-teal btn-sm px-3 py-2">
          <i class="bi bi-arrow-left me-1"></i> Back to Users
        </a>
      </div>
    </div>
  </div>

  <!-- Box 2: Form Card (Electric Purple & Indigo Box) -->
  <div class="card dashboard-card box-purple p-4 p-md-5 mb-4 animate-slide-up-2">
    <div class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom border-secondary border-opacity-25">
      <span class="icon-box-purple"><i class="bi <?= $editing ? 'bi-person-gear' : 'bi-person-plus' ?>"></i></span>
      <div>
        <h2 class="h5 fw-bold mb-0 text-white"><?= $editing ? 'Account Details' : 'New User Details' ?></h2>
        <span class="text-secondary small">Set login email, role access, and password</span>
      </div>
    </div>

    <form method="post" novalidate class="needs-validation">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label fw-semibold text-light" for="full_name">Full Name <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person text-info"></i></span>
          <input class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= e($u['full_name']) ?>" required minlength="2" maxlength="100" placeholder="e.g. John Doe">
          <div class="invalid-feedback"><?= e($errors['full_name'] ?? 'Enter a name (2-100 characters).') ?></div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold text-light" for="email">Email Address <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope text-info"></i></span>
          <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= e($u['email']) ?>" required placeholder="name@example.com">
          <div class="invalid-feedback"><?= e($errors['email'] ?? 'Enter a valid email address.') ?></div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label fw-semibold text-light" for="phone">Phone Number</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-telephone text-success"></i></span>
            <input class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= e($u['phone']) ?>" pattern="\+?[0-9 \-]{7,15}" placeholder="+1 234 567 8900">
            <div class="invalid-feedback"><?= e($errors['phone'] ?? 'Enter a valid phone number.') ?></div>
          </div>
        </div>

        <div class="col-sm-6">
          <label class="form-label fw-semibold text-light" for="role">User Role <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield text-warning"></i></span>
            <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" id="role" name="role" required>
              <?php foreach ($roles as $r): ?>
                <option value="<?= e($r['name']) ?>" <?= $u['role'] === $r['name'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= e($errors['role'] ?? 'Choose a role.') ?></div>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold text-light" for="password">Password <?= $editing ? '<span class="text-secondary small fw-normal">(leave blank to keep current)</span>' : '<span class="text-danger">*</span>' ?></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock text-danger"></i></span>
          <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" <?= $editing ? '' : 'required' ?> autocomplete="new-password" placeholder="<?= $editing ? 'Leave blank to preserve password' : 'Min 8 chars, letter & number' ?>">
          <button class="btn btn-outline-secondary toggle-pw" type="button" tabindex="-1"><i class="bi bi-eye"></i></button>
          <div class="invalid-feedback"><?= e($errors['password'] ?? 'Min 8 characters with a letter and a number.') ?></div>
        </div>
      </div>

      <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top border-secondary border-opacity-25">
        <a href="users.php" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
        <button class="btn btn-teal px-4 py-2 shadow">
          <i class="bi bi-check2-circle me-1"></i> <?= $editing ? 'Update User' : 'Create User' ?>
        </button>
      </div>
    </form>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
