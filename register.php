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
  <div class="card auth-card dashboard-card box-multicolor shadow-lg animate-slide-up-1">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3" style="background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3);">
          <i class="bi bi-person-plus text-purple fs-2" style="color: #a855f7;"></i>
        </div>
        <h1 class="h3 fw-bold mb-1 text-white">Create Account</h1>
        <p class="text-secondary small mb-0">Join the User Management Portal.</p>
      </div>
      <?= flash_render() ?>
      <form method="post" novalidate class="needs-validation">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label text-light fw-medium small" for="full_name">Full Name</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person text-info"></i></span>
            <input class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" required minlength="2" maxlength="100" placeholder="e.g. Subha Mahata">
            <div class="invalid-feedback"><?= e($errors['full_name'] ?? 'Enter your name (2-100 characters).') ?></div>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label text-light fw-medium small" for="email">Email Address</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope text-info"></i></span>
            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= e($old['email']) ?>" required placeholder="name@example.com">
            <div class="invalid-feedback"><?= e($errors['email'] ?? 'Enter a valid email address.') ?></div>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label text-light fw-medium small" for="password">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock text-info"></i></span>
            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" placeholder="Min 8 chars with letter & digit">
            <button class="btn btn-outline-secondary toggle-pw" type="button" tabindex="-1"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback"><?= e($errors['password'] ?? 'Min 8 characters with a letter and a number.') ?></div>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label text-light fw-medium small" for="confirm">Confirm Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-check text-info"></i></span>
            <input type="password" class="form-control <?= isset($errors['confirm']) ? 'is-invalid' : '' ?>" id="confirm" name="confirm" required placeholder="Re-enter your password">
            <div class="invalid-feedback"><?= e($errors['confirm'] ?? 'Passwords must match.') ?></div>
          </div>
        </div>
        <button class="btn btn-purple w-100 py-2 fw-bold shadow">
          <i class="bi bi-person-check-fill me-1"></i> Register Account
        </button>
      </form>
      <p class="text-center text-secondary mt-4 mb-0 small">Already have an account? <a href="login.php" class="text-info fw-semibold">Log in</a></p>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
