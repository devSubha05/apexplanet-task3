<?php
require __DIR__ . '/../includes/init.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/users.php');
csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id === (int)$_SESSION['user_id']) {
    flash('danger', 'You cannot delete your own account.');
} elseif ($u = get_user($conn, $id)) {
    $st = $conn->prepare('DELETE FROM users WHERE id = ?');
    $st->bind_param('i', $id);
    $st->execute();
    delete_avatar($u['profile_picture']);
    flash('success', 'User deleted.');
} else {
    flash('danger', 'User not found.');
}
redirect('admin/users.php');
