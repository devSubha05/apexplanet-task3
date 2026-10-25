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
  <div class="card auth-card shadow">
    <div class="card-body p-4 p-md-5">
      <h1 class="h3 text-teal fw-bold mb-1">Welcome back</h1>
      <p class="text-muted mb-4">Log in to continue.</p>
      <?= flash_render() ?>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <form method="post" novalidate class="needs-validation">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" required autofocus>
          <div class="invalid-feedback">Enter a valid email address.</div>
        </div>
        <div class="mb-4">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required>
            <button class="btn btn-outline-secondary toggle-pw" type="button" tabindex="-1"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback">Enter your password.</div>
          </div>
        </div>
        <button class="btn btn-teal w-100 py-2">Log in</button>
      </form>
      <p class="text-center mt-4 mb-0">New here? <a href="register.php">Create an account</a></p>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
