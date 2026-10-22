<?php
require __DIR__ . '/includes/init.php';
require_login();
$id = (int)$_SESSION['user_id'];
$u  = get_user($conn, $id);
if (!$u) { session_destroy(); redirect('login.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name  = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio   = trim($_POST['bio'] ?? '');
    $cur   = $_POST['current_password'] ?? '';
    $new   = $_POST['new_password'] ?? '';

    if ($m = v_name($name))   $errors['full_name'] = $m;
    if ($m = v_phone($phone)) $errors['phone'] = $m;
    if ($m = v_bio($bio))     $errors['bio'] = $m;

    $newHash = null;
    if ($new !== '' || $cur !== '') {
        if (!password_verify($cur, $u['password_hash'])) $errors['current_password'] = 'Current password is incorrect.';
        if ($m = v_password($new)) $errors['new_password'] = $m;
        if (!$errors) $newHash = password_hash($new, PASSWORD_DEFAULT);
    }

    $newPic = null;
    if (!$errors) {
        [$newPic, $picErr] = save_avatar($_FILES['avatar'] ?? []);
        if ($picErr) $errors['avatar'] = $picErr;
    }

    if (!$errors) {
        $phoneDb = $phone !== '' ? $phone : null;
        $bioDb   = $bio !== '' ? $bio : null;
        $pic     = $newPic ?? $u['profile_picture'];
        $hash    = $newHash ?? $u['password_hash'];
        $st = $conn->prepare('UPDATE users SET full_name = ?, phone = ?, bio = ?, profile_picture = ?, password_hash = ? WHERE id = ?');
        $st->bind_param('sssssi', $name, $phoneDb, $bioDb, $pic, $hash, $id);
        $st->execute();
        if ($newPic) delete_avatar($u['profile_picture']);      // remove the old picture
        $_SESSION['name'] = $name;
        flash('success', 'Profile updated successfully.');
        redirect('profile.php');
    }
    $u['full_name'] = $name; $u['phone'] = $phone; $u['bio'] = $bio;   // keep typed values
}
$pageTitle = 'Edit Profile';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4" style="max-width:820px">
  <?= flash_render() ?>
  <div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Edit profile</div>
    <div class="card-body p-4">
      <form method="post" enctype="multipart/form-data" novalidate class="needs-validation">
        <?= csrf_field() ?>
        <div class="row g-4">
          <div class="col-md-4 text-center">
            <div class="mb-3" id="avatar-preview-wrap"><?= avatar_html($u, 140) ?></div>
            <input type="file" class="form-control form-control-sm <?= isset($errors['avatar']) ? 'is-invalid' : '' ?>" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" data-max="<?= MAX_UPLOAD_BYTES ?>">
            <div class="invalid-feedback" id="avatar-error"><?= e($errors['avatar'] ?? '') ?></div>
            <div class="form-text">JPG, PNG or WEBP, max 2 MB.</div>
          </div>
          <div class="col-md-8">
            <div class="mb-3">
              <label class="form-label" for="full_name">Full name</label>
              <input class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= e($u['full_name']) ?>" required minlength="2" maxlength="100">
              <div class="invalid-feedback"><?= e($errors['full_name'] ?? 'Enter your name (2-100 characters).') ?></div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" id="email" value="<?= e($u['email']) ?>" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label" for="phone">Phone</label>
              <input class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= e($u['phone']) ?>" pattern="\+?[0-9 \-]{7,15}">
              <div class="invalid-feedback"><?= e($errors['phone'] ?? 'Enter a valid phone number.') ?></div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="bio">Bio</label>
              <textarea class="form-control <?= isset($errors['bio']) ? 'is-invalid' : '' ?>" id="bio" name="bio" rows="3" maxlength="500"><?= e($u['bio']) ?></textarea>
              <div class="invalid-feedback"><?= e($errors['bio'] ?? 'Max 500 characters.') ?></div>
            </div>
            <hr>
            <p class="text-muted small mb-2">Change password (leave blank to keep the current one)</p>
            <div class="row g-3">
              <div class="col-sm-6">
                <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" name="current_password" placeholder="Current password" autocomplete="current-password">
                <div class="invalid-feedback"><?= e($errors['current_password'] ?? '') ?></div>
              </div>
              <div class="col-sm-6">
                <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" name="new_password" placeholder="New password" autocomplete="new-password">
                <div class="invalid-feedback"><?= e($errors['new_password'] ?? '') ?></div>
              </div>
            </div>
          </div>
        </div>
        <div class="text-end mt-4">
          <a href="dashboard.php" class="btn btn-light">Cancel</a>
          <button class="btn btn-teal">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
