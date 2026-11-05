<?php
if (user()) redirect('dashboard');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = q('SELECT * FROM users WHERE username = ?', [trim($_POST['username'] ?? '')])->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        unset($u['password']);
        $_SESSION['user'] = $u;
        redirect('dashboard');
    }
    $error = 'Username atau password salah.';
}
$title = 'Login Panitia';
require __DIR__ . '/../../includes/pub_header.php';
?>
<form method="post" class="box" style="max-width:400px;margin:30px auto"><div class="box-body" style="padding:28px">
    <?= csrf_field() ?>
    <h1 style="font-size:24px">Login panitia</h1>
    <p class="muted" style="margin:4px 0 20px">Panel verifikasi dan seleksi PPDB.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <div style="margin-bottom:12px"><label>Username</label><input type="text" name="username" required autofocus></div>
    <div style="margin-bottom:18px"><label>Password</label><input type="password" name="password" required></div>
    <button class="btn btn-primary btn-block">Masuk</button>
    <div class="callout" style="margin:18px 0 0;font-size:13px">Akun demo: <b>admin</b> / <b>admin123</b> atau <b>verifikator</b> / <b>admin123</b></div>
</div></form>
<?php require __DIR__ . '/../../includes/pub_footer.php'; ?>
