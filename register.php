<?php
require __DIR__ . '/includes/init.php';
guest_only();

$errors = []; $old = ['full_name' => '', 'email' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    $conf = $_POST['confirm'] ?? '';

    if ($m = v_name($old['full_name'])) $errors['full_name'] = $m;
    if ($m = v_email($old['email']))    $errors['email'] = $m;
    elseif (email_taken($conn, $old['email'])) $errors['email'] = 'This email is already registered.';
    if ($m = v_password($pass))         $errors['password'] = $m;
    if ($pass !== $conf)                $errors['confirm'] = 'Passwords do not match.';

    if (!$errors) {
        $rid  = role_id($conn, 'User');  
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $st = $conn->prepare('INSERT INTO users (role_id, full_name, email, password_hash) VALUES (?, ?, ?, ?)');
        $st->bind_param('isss', $rid, $old['full_name'], $old['email'], $hash);
        $st->execute();
        flash('success', 'Registration successful! Please log in.');
        redirect('login.php');
    }
}
$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<main class="container auth-wrap">
  <div class="card auth-card shadow">
    <div class="card-body p-4 p-md-5">
      <h1 class="h3 text-teal fw-bold mb-1">Create account</h1>
      <p class="text-muted mb-4">Join the User Management System.</p>
      <?= flash_render() ?>
      <form method="post" novalidate class="needs-validation">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label" for="full_name">Full name</label>
          <input class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" required minlength="2" maxlength="100">
          <div class="invalid-feedback"><?= e($errors['full_name'] ?? 'Enter your name (2-100 characters).') ?></div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= e($old['email']) ?>" required>
          <div class="invalid-feedback"><?= e($errors['email'] ?? 'Enter a valid email address.') ?></div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}">
            <button class="btn btn-outline-secondary toggle-pw" type="button" tabindex="-1"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback"><?= e($errors['password'] ?? 'Min 8 characters with a letter and a number.') ?></div>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label" for="confirm">Confirm password</label>
          <input type="password" class="form-control <?= isset($errors['confirm']) ? 'is-invalid' : '' ?>" id="confirm" name="confirm" required>
          <div class="invalid-feedback"><?= e($errors['confirm'] ?? 'Passwords must match.') ?></div>
        </div>
        <button class="btn btn-teal w-100 py-2">Register</button>
      </form>
      <p class="text-center mt-4 mb-0">Already have an account? <a href="login.php">Log in</a></p>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
