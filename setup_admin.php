<?php
// Run ONCE from the terminal:  php setup_admin.php "Admin Name" admin@example.com "StrongPass123"
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Run this script from the command line only.'); }
require __DIR__ . '/includes/init.php';
[$script, $name, $email, $pass] = $argv + [null, null, null, null];
if (!$name || !$email || !$pass) exit("Usage: php setup_admin.php \"Name\" email password\n");
foreach ([v_name($name), v_email($email), v_password($pass)] as $err) if ($err) exit("Error: $err\n");
if (email_taken($conn, $email)) exit("Error: that email already exists.\n");
$rid = role_id($conn, 'Admin');
$hash = password_hash($pass, PASSWORD_DEFAULT);
$st = $conn->prepare('INSERT INTO users (role_id, full_name, email, password_hash) VALUES (?, ?, ?, ?)');
$st->bind_param('isss', $rid, $name, $email, $hash);
$st->execute();
echo "Admin created: $email\n";
