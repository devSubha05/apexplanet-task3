# User Management System (PHP + MySQL)

Task 3 - Backend Development & Database Integration.

A responsive user management system with authentication, role-based access (User / Admin), full CRUD, and profile management with picture upload.

## Features

- **Database**: `roles` + `users` tables in 3NF with a foreign key (see [docs/ER_diagram.md](docs/ER_diagram.md))
- **Authentication**: registration (`password_hash`), login/logout with sessions, role-based redirect (Admin -> user manager, User -> dashboard)
- **CRUD (Admin)**: add user (PHP form), list users in an HTML table with search, update user, delete with a confirmation popup (Bootstrap modal)
- **Profile**: edit name / phone / bio, change password, upload profile picture (JPG/PNG/WEBP, max 2 MB), dynamic display on the dashboard
- **Security**: prepared statements (`mysqli_prepare`) everywhere, server-side validation on every input, bcrypt password hashing, CSRF tokens on all POST forms, session ID regeneration on login, output escaping (XSS), upload checks by real MIME type + `getimagesize` + random filenames, script execution blocked in the upload folder
- **UI**: Bootstrap 5, mobile-first responsive layout, client-side validation as a convenience

## Tech stack

PHP 8.1+, MySQL / MariaDB, Bootstrap 5, vanilla JS. (Needs the `mysqli` and `fileinfo` extensions - both are on by default in XAMPP.)

## Setup (XAMPP / WAMP)

1. Copy this folder to `htdocs/user-management`.
2. Start **Apache** and **MySQL**.
3. Import the schema: open phpMyAdmin -> *Import* -> choose `database.sql`.
4. Check `config/db.php` (DB user/password, and `BASE_URL` if you used a different folder name).
5. Create the first admin from a terminal in the project folder:
   ```
   php setup_admin.php "Admin Name" admin@example.com "Admin1234"
   ```
6. Open http://localhost/user-management/ - log in as the admin, or register a normal user.

## Project structure

```
config/db.php          DB connection + BASE_URL
includes/init.php      session, CSRF, auth guards, validators, upload helper
includes/header.php    navbar / layout        includes/footer.php  scripts
register.php login.php logout.php dashboard.php profile.php
admin/users.php        list + search + delete modal
admin/user_form.php    add / update user      admin/delete_user.php  delete handler
assets/                css + js               uploads/avatars/       profile pictures
database.sql           schema + seed roles    setup_admin.php        CLI admin creator
docs/ER_diagram.md     ER diagram + 1NF/2NF/3NF
```

## Demo video checklist (8 min)

1. Show ER diagram + explain normalization (1 min)
2. Register a user -> show hashed password in phpMyAdmin (1.5 min)
3. Login as User: dashboard, edit profile, upload picture, show invalid file/size error (2 min)
4. Login as Admin: add, search, update, delete with popup (2 min)
5. Security: prepared statements in code, server-side validation, CSRF, role protection by opening `/admin/users.php` as a User (1.5 min)

## Screenshots

_Add screenshots of the login page, dashboard, user table and edit profile here._
