<?php
if (pendaftar()) redirect('formulir');
$jalur = q('SELECT * FROM jalur ORDER BY id')->fetchAll();
$error = null;
$d = ['jalur_id' => '', 'nisn' => '', 'nama' => '', 'tgl_lahir' => '', 'telepon' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($d as $k => $_) $d[$k] = trim($_POST[$k] ?? '');
    try {
        if (tahap() !== 'pendaftaran') throw new RuntimeException('Pendaftaran sedang tidak dibuka.');
        if (!preg_match('/^\d{10}$/', $d['nisn'])) throw new RuntimeException('NISN harus 10 digit angka.');
        if ($d['nama'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $d['tgl_lahir']) || !preg_match('/^[0-9+]{9,15}$/', $d['telepon'])) throw new RuntimeException('Lengkapi nama, tanggal lahir, dan nomor WhatsApp yang valid.');
        if (!q('SELECT 1 FROM jalur WHERE id = ?', [(int) $d['jalur_id']])->fetchColumn()) throw new RuntimeException('Pilih jalur pendaftaran.');
        if (q('SELECT 1 FROM pendaftar WHERE nisn = ?', [$d['nisn']])->fetchColumn()) throw new RuntimeException('NISN ini sudah terdaftar. Gunakan menu Masuk untuk melanjutkan.');
        q('INSERT INTO pendaftar (no_daftar, jalur_id, nisn, nama, tgl_lahir, telepon) VALUES (?,?,?,?,?,?)', ['TMP-' . bin2hex(random_bytes(4)), (int) $d['jalur_id'], $d['nisn'], $d['nama'], $d['tgl_lahir'], $d['telepon']]);
        $id = (int) db()->lastInsertId();
        $no = 'PPDB-' . substr(setting()['tahun_ajaran'], 0, 4) . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
        q('UPDATE pendaftar SET no_daftar = ? WHERE id = ?', [$no, $id]);
        session_regenerate_id(true);
        $_SESSION['pendaftar_id'] = $id;
        flash("Akun dibuat. Nomor pendaftaranmu $no — simpan nomor ini untuk masuk kembali.");
        redirect('formulir');
    } catch (RuntimeException $ex) {
        $error = $ex->getMessage();
    }
}
$title = 'Daftar';
require __DIR__ . '/../../includes/pub_header.php';
?>
<form method="post" class="box" style="max-width:560px;margin:10px auto"><div class="box-body" style="padding:26px">
    <?= csrf_field() ?>
    <h1 style="font-size:26px">Buat akun pendaftaran</h1>
    <p class="muted" style="margin:4px 0 20px">Langkah 1 dari 4. Setelah ini kamu bisa melengkapi formulir kapan saja sebelum pendaftaran ditutup.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <div class="form-grid">
        <div class="full"><label>Jalur pendaftaran</label><select name="jalur_id" required><option value="">Pilih jalur</option><?php foreach ($jalur as $j): ?><option value="<?= $j['id'] ?>" <?= $d['jalur_id'] == $j['id'] ? 'selected' : '' ?>><?= e($j['nama']) ?> (kuota <?= $j['kuota'] ?>)</option><?php endforeach; ?></select></div>
        <div><label>NISN</label><input type="text" name="nisn" maxlength="10" inputmode="numeric" value="<?= e($d['nisn']) ?>" required></div>
        <div><label>Tanggal lahir</label><input type="date" name="tgl_lahir" value="<?= e($d['tgl_lahir']) ?>" required></div>
        <div class="full"><label>Nama lengkap (sesuai akta)</label><input type="text" name="nama" value="<?= e($d['nama']) ?>" required></div>
        <div class="full"><label>No. WhatsApp orang tua</label><input type="text" name="telepon" value="<?= e($d['telepon']) ?>" required></div>
    </div>
    <button class="btn btn-primary btn-block btn-lg mt" style="margin-top:20px">Buat akun & lanjutkan</button>
    <p class="muted" style="font-size:13px;margin:14px 0 0;text-align:center">Sudah punya nomor pendaftaran? <a href="<?= url('masuk') ?>">Masuk</a></p>
</div></form>
<?php require __DIR__ . '/../../includes/pub_footer.php'; ?>
