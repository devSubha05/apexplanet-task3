<?php
// Handles both ADD (no id) and UPDATE (?id=) of a user.
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
            flash('success', 'User updated.');
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $st = $conn->prepare('INSERT INTO users (role_id, full_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)');
            $st->bind_param('issss', $rid, $name, $email, $phoneDb, $hash);
            $st->execute();
            flash('success', 'User added.');
        }
        redirect('admin/users.php');
    }
    $u = ['full_name' => $name, 'email' => $email, 'phone' => $phone, 'role' => $role];
}
$pageTitle = $editing ? 'Edit User' : 'Add User';
require __DIR__ . '/../includes/header.php';
?>
<main class="container py-4" style="max-width:640px">
  <div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold"><?= $editing ? 'Update user' : 'Add new user' ?></div>
    <div class="card-body p-4">
      <form method="post" novalidate class="needs-validation">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label" for="full_name">Full name</label>
          <input class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= e($u['full_name']) ?>" required minlength="2" maxlength="100">
          <div class="invalid-feedback"><?= e($errors['full_name'] ?? 'Enter a name (2-100 characters).') ?></div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= e($u['email']) ?>" required>
          <div class="invalid-feedback"><?= e($errors['email'] ?? 'Enter a valid email address.') ?></div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-sm-6">
            <label class="form-label" for="phone">Phone</label>
            <input class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= e($u['phone']) ?>" pattern="\+?[0-9 \-]{7,15}">
            <div class="invalid-feedback"><?= e($errors['phone'] ?? 'Enter a valid phone number.') ?></div>
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="role">Role</label>
            <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" id="role" name="role" required>
              <?php foreach ($roles as $r): ?>
                <option value="<?= e($r['name']) ?>" <?= $u['role'] === $r['name'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= e($errors['role'] ?? 'Choose a role.') ?></div>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label" for="password">Password <?= $editing ? '<span class="text-muted small">(leave blank to keep current)</span>' : '' ?></label>
          <div class="input-group">
            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" <?= $editing ? '' : 'required' ?> autocomplete="new-password">
            <button class="btn btn-outline-secondary toggle-pw" type="button" tabindex="-1"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback"><?= e($errors['password'] ?? 'Min 8 characters with a letter and a number.') ?></div>
          </div>
        </div>
        <div class="text-end">
          <a href="users.php" class="btn btn-light">Cancel</a>
          <button class="btn btn-teal"><?= $editing ? 'Update' : 'Add user' ?></button>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
