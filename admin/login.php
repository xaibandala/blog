<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    // Use password_hash + password_verify
    $hash = password_hash(ADMIN_PASSWORD_PLAIN, PASSWORD_DEFAULT);

    if ($username === ADMIN_USERNAME && password_verify($password, $hash)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = ADMIN_USERNAME;
        header('Location: /blog/admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login · Simple Blog</title>
  <meta name="theme-color" content="#111827">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/blog/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container min-vh-100 d-flex align-items-center">
<div class="row justify-content-center w-100">
  <div class="col-md-6 col-lg-4 mx-auto">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Admin Login</h5>
        <?php if ($error): ?>
          <div class="alert alert-danger py-2"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required autocomplete="username">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input id="passwordInput" type="password" name="password" class="form-control" required autocomplete="current-password">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle password visibility" aria-pressed="false">Show</button>
            </div>
          </div>
          <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
      </div>
    </div>
  </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/blog/assets/js/main.js"></script>
  <script>
    (function(){
      var btn = document.getElementById('togglePassword');
      var input = document.getElementById('passwordInput');
      if (!btn || !input) return;
      btn.addEventListener('click', function(){
        var isText = input.getAttribute('type') === 'text';
        input.setAttribute('type', isText ? 'password' : 'text');
        btn.setAttribute('aria-pressed', String(!isText));
        btn.textContent = isText ? 'Show' : 'Hide';
      });
    })();
  </script>
</div>
</body>
</html>
