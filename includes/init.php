<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

const MAX_UPLOAD_BYTES = 2 * 1024 * 1024;
const UPLOAD_DIR = __DIR__ . '/../uploads/avatars/';

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . BASE_URL . '/' . ltrim($path, '/')); exit; }

function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }
function flash_render(): string {
    $out = '';
    foreach ($_SESSION['flash'] ?? [] as [$t, $m]) {
        $out .= '<div class="alert alert-' . e($t) . ' alert-dismissible fade show" role="alert">' . e($m)
              . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Invalid request (CSRF check failed).');
    }
}

function is_logged_in(): bool { return isset($_SESSION['user_id']); }
function is_admin(): bool { return ($_SESSION['role'] ?? '') === 'Admin'; }
function require_login(): void { if (!is_logged_in()) { flash('warning', 'Please log in first.'); redirect('login.php'); } }
function require_admin(): void {
    require_login();
    if (!is_admin()) { http_response_code(403); exit('403 - Admins only.'); }
}
function guest_only(): void { if (is_logged_in()) redirect(is_admin() ? 'admin/users.php' : 'dashboard.php'); }

function get_user(mysqli $c, int $id): ?array {
    $st = $c->prepare('SELECT u.*, r.name AS role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?');
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    return $row ?: null;
}
function role_id(mysqli $c, string $name): ?int {
    $st = $c->prepare('SELECT id FROM roles WHERE name = ?');
    $st->bind_param('s', $name);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    return $row ? (int)$row['id'] : null;
}

function v_name(string $s): string {
    if ($s === '' || mb_strlen($s) < 2 || mb_strlen($s) > 100) return 'Name must be 2-100 characters.';
    if (!preg_match("/^[\p{L} .'-]+$/u", $s)) return 'Name can only contain letters, spaces, . \' and -.';
    return '';
}
function v_email(string $s): string {
    return (filter_var($s, FILTER_VALIDATE_EMAIL) && mb_strlen($s) <= 150) ? '' : 'Enter a valid email address.';
}
function v_password(string $s): string {
    if (strlen($s) < 8) return 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Za-z]/', $s) || !preg_match('/\d/', $s)) return 'Password needs at least one letter and one number.';
    return '';
}
function v_phone(string $s): string {
    return ($s === '' || preg_match('/^\+?[0-9 \-]{7,15}$/', $s)) ? '' : 'Enter a valid phone number.';
}
function v_bio(string $s): string { return mb_strlen($s) <= 500 ? '' : 'Bio can be at most 500 characters.'; }

function email_taken(mysqli $c, string $email, int $exceptId = 0): bool {
    $st = $c->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
    $st->bind_param('si', $email, $exceptId);
    $st->execute();
    $st->store_result();
    $taken = $st->num_rows > 0;
    $st->close();
    return $taken;
}

function save_avatar(array $f): array {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [null, ''];
    if ($f['error'] !== UPLOAD_ERR_OK) return [null, 'Upload failed. Try again.'];
    if ($f['size'] > MAX_UPLOAD_BYTES) return [null, 'Image must be 2 MB or smaller.'];
    if (!is_uploaded_file($f['tmp_name'])) return [null, 'Invalid upload.'];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime]) || @getimagesize($f['tmp_name']) === false) {
        return [null, 'Only JPG, PNG or WEBP images are allowed.'];
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $name)) return [null, 'Could not save the image.'];
    return [$name, ''];
}
function delete_avatar(?string $file): void {
    if ($file && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $file)) @unlink(UPLOAD_DIR . $file);
}
function avatar_html(array $u, int $size = 40): string {
    $s = (int)$size;
    if (!empty($u['profile_picture'])) {
        return '<img class="avatar" width="' . $s . '" height="' . $s . '" src="' . BASE_URL . '/uploads/avatars/' . e($u['profile_picture']) . '" alt="Avatar of ' . e($u['full_name']) . '">';
    }
    $ini = mb_strtoupper(mb_substr($u['full_name'], 0, 1));
    return '<span class="avatar avatar-initial" style="width:' . $s . 'px;height:' . $s . 'px;font-size:' . round($s / 2.3) . 'px">' . e($ini) . '</span>';
}
