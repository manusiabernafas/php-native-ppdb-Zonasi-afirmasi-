<?php
if (pendaftar()) redirect('status');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['coba'] = array_filter($_SESSION['coba'] ?? [], fn ($t) => $t > time() - 600);
    $p = q('SELECT * FROM pendaftar WHERE no_daftar = ? AND tgl_lahir = ?', [strtoupper(trim($_POST['no_daftar'] ?? '')), $_POST['tgl_lahir'] ?? ''])->fetch();
    if (count($_SESSION['coba']) >= 5) $error = 'Terlalu banyak percobaan. Coba lagi 10 menit lagi.';
    elseif (!$p) { $_SESSION['coba'][] = time(); $error = 'Nomor pendaftaran atau tanggal lahir tidak cocok.'; }
    else { session_regenerate_id(true); $_SESSION['pendaftar_id'] = $p['id']; redirect('status'); }
}
$title = 'Masuk';
require __DIR__ . '/../../includes/pub_header.php';
?>
<form method="post" class="box" style="max-width:440px;margin:10px auto"><div class="box-body" style="padding:26px">
    <?= csrf_field() ?>
    <h1 style="font-size:26px">Masuk</h1>
    <p class="muted" style="margin:4px 0 20px">Lanjutkan pendaftaran atau cek status.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <div style="margin-bottom:12px"><label>Nomor pendaftaran</label><input type="text" name="no_daftar" placeholder="PPDB-2026-0001" required class="mono"></div>
    <div style="margin-bottom:18px"><label>Tanggal lahir</label><input type="date" name="tgl_lahir" required></div>
    <button class="btn btn-primary btn-block btn-lg">Masuk</button>
</div></form>
<?php require __DIR__ . '/../../includes/pub_footer.php'; ?>
