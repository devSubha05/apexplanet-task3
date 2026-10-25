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
        if ($newPic) delete_avatar($u['profile_picture']);
        $_SESSION['name'] = $name;
        flash('success', 'Profile updated successfully.');
        redirect('profile.php');
    }
    $u['full_name'] = $name; $u['phone'] = $phone; $u['bio'] = $bio;
}
$pageTitle = 'Edit Profile';
$bodyClass = 'dashboard-bg';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4 dashboard-main" style="max-width:880px">
  <?= flash_render() ?>

  <div class="card dashboard-card box-cyan p-4 mb-4 animate-slide-up-1">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-info"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active text-light" aria-current="page">Edit Profile</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-1 text-white">Profile Settings</h1>
        <p class="text-secondary mb-0 small">Update your personal details, avatar photo, and account credentials.</p>
      </div>
      <div>
        <a href="dashboard.php" class="btn btn-outline-teal btn-sm px-3 py-2">
          <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
      </div>
    </div>
  </div>

  <div class="card dashboard-card box-purple p-4 p-md-5 mb-4 animate-slide-up-2">
    <div class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom border-secondary border-opacity-25">
      <span class="icon-box-purple"><i class="bi bi-pencil-square"></i></span>
      <div>
        <h2 class="h5 fw-bold mb-0 text-white">Personal Information</h2>
        <span class="text-secondary small">Manage your identity and public profile</span>
      </div>
    </div>

    <form method="post" enctype="multipart/form-data" novalidate class="needs-validation">
      <?= csrf_field() ?>
      <div class="row g-4">

        <div class="col-md-4 text-center">
          <div class="p-3 rounded-4 border box-emerald" style="background: rgba(15, 23, 42, 0.6); border-color: rgba(255, 255, 255, 0.08) !important;">
            <label class="form-label fw-bold text-info small mb-3 d-block text-uppercase" style="letter-spacing:0.04em;">Profile Photo</label>
            <div class="mb-3 avatar-wrapper" id="avatar-preview-wrap">
              <?= avatar_html($u, 140) ?>
            </div>
            <input type="file" class="form-control form-control-sm <?= isset($errors['avatar']) ? 'is-invalid' : '' ?>" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" data-max="<?= MAX_UPLOAD_BYTES ?>">
            <div class="invalid-feedback text-start" id="avatar-error"><?= e($errors['avatar'] ?? '') ?></div>
            <div class="text-secondary small mt-2">JPG, PNG or WEBP (Max 2 MB)</div>
          </div>
        </div>

        <div class="col-md-8">
          <div class="mb-3">
            <label class="form-label fw-semibold text-light" for="full_name">Full Name <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person text-info"></i></span>
              <input class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= e($u['full_name']) ?>" required minlength="2" maxlength="100" placeholder="Your full name">
              <div class="invalid-feedback"><?= e($errors['full_name'] ?? 'Enter your name (2-100 characters).') ?></div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold text-light" for="email">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope text-secondary"></i></span>
              <input class="form-control" id="email" value="<?= e($u['email']) ?>" disabled>
            </div>
            <div class="text-secondary small mt-1">Email is locked for account safety.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold text-light" for="phone">Phone Number</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-telephone text-success"></i></span>
              <input class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= e($u['phone']) ?>" pattern="\+?[0-9 \-]{7,15}" placeholder="+1 234 567 8900">
              <div class="invalid-feedback"><?= e($errors['phone'] ?? 'Enter a valid phone number.') ?></div>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold text-light" for="bio">Bio</label>
            <textarea class="form-control <?= isset($errors['bio']) ? 'is-invalid' : '' ?>" id="bio" name="bio" rows="3" maxlength="500" placeholder="Tell us a little bit about yourself..."><?= e($u['bio']) ?></textarea>
            <div class="invalid-feedback"><?= e($errors['bio'] ?? 'Max 500 characters.') ?></div>
          </div>

          <div class="p-3 rounded-3 border mb-4 box-rose" style="background: rgba(15, 23, 42, 0.65); border-color: rgba(255, 255, 255, 0.08) !important;">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="icon-box-rose" style="width:34px; height:34px; font-size:1rem;"><i class="bi bi-shield-lock"></i></span>
              <span class="fw-semibold text-white">Change Password</span>
            </div>
            <p class="text-secondary small mb-3">Leave both inputs empty if you want to keep your current password.</p>
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label small text-secondary">Current Password</label>
                <div class="input-group">
                  <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" name="current_password" placeholder="Current password" autocomplete="current-password">
                  <button class="btn btn-outline-secondary toggle-pw" type="button" tabindex="-1"><i class="bi bi-eye"></i></button>
                  <div class="invalid-feedback"><?= e($errors['current_password'] ?? '') ?></div>
                </div>
              </div>
              <div class="col-sm-6">
                <label class="form-label small text-secondary">New Password</label>
                <div class="input-group">
                  <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" name="new_password" placeholder="New password (min 8 chars)" autocomplete="new-password">
                  <button class="btn btn-outline-secondary toggle-pw" type="button" tabindex="-1"><i class="bi bi-eye"></i></button>
                  <div class="invalid-feedback"><?= e($errors['new_password'] ?? '') ?></div>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-3 border-top border-secondary border-opacity-25">
            <a href="dashboard.php" class="btn btn-outline-secondary px-4 py-2">
              Cancel
            </a>
            <button class="btn btn-teal px-4 py-2 shadow">
              <i class="bi bi-check2-circle me-1"></i> Save Changes
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
