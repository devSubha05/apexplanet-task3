<?php
require __DIR__ . '/includes/init.php';
guest_only();

$error = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    $st = $conn->prepare('SELECT u.id, u.full_name, u.password_hash, r.name AS role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = ?');
    $st->bind_param('s', $email);
    $st->execute();
    $user = $st->get_result()->fetch_assoc();
    $st->close();

    if ($user && password_verify($pass, $user['password_hash'])) {
        session_regenerate_id(true);                          
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['name']    = $user['full_name'];
        $_SESSION['role']    = $user['role'];
        flash('success', 'Welcome back, ' . $user['full_name'] . '!');
        redirect($user['role'] === 'Admin' ? 'admin/users.php' : 'dashboard.php');   
    }
    $error = 'Invalid email or password.';
}
$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<main class="container auth-wrap">
  <div class="card auth-card dashboard-card box-multicolor shadow-lg animate-slide-up-1">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3" style="background: rgba(20, 184, 166, 0.15); border: 1px solid rgba(20, 184, 166, 0.3);">
          <i class="bi bi-shield-lock text-teal fs-2"></i>
        </div>
        <h1 class="h3 fw-bold mb-1 text-white">Welcome back</h1>
        <p class="text-secondary small mb-0">Sign in to your UserMS portal.</p>
      </div>
      <?= flash_render() ?>
      <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
      <form method="post" novalidate class="needs-validation">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label text-light fw-medium small" for="email">Email Address</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope text-info"></i></span>
            <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" required autofocus placeholder="name@example.com">
            <div class="invalid-feedback">Enter a valid email address.</div>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label text-light fw-medium small" for="password">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock text-info"></i></span>
            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
            <button class="btn btn-outline-secondary toggle-pw" type="button" tabindex="-1"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback">Enter your password.</div>
          </div>
        </div>
        <button class="btn btn-teal w-100 py-2 fw-bold shadow">
          <i class="bi bi-box-arrow-in-right me-1"></i> Log in
        </button>
      </form>
      <p class="text-center text-secondary mt-4 mb-0 small">New here? <a href="register.php" class="text-info fw-semibold">Create an account</a></p>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
