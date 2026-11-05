<?php
$p = pendaftar();
if (!$p) redirect('masuk');
$bisaUbah = in_array($p['status'], ['draft', 'perbaikan'], true) && tahap() === 'pendaftaran';
$tab = $_GET['tab'] ?? 'biodata';
$berkasMap = [];
foreach (q('SELECT * FROM berkas WHERE pendaftar_id = ?', [$p['id']])->fetchAll() as $b) $berkasMap[$b['jenis']] = $b;
$wajib = ['kk', 'akta', 'rapor', 'foto'];
if ($p['jalur'] !== 'Zonasi') $wajib[] = 'pendukung';

// Kelengkapan tiap langkah
$lengkap = [
    'biodata' => $p['nik'] && $p['tempat_lahir'] && $p['asal_sekolah'] && $p['alamat'],
    'ortu' => $p['nama_ayah'] && $p['nama_ibu'],
    'nilai' => $p['nilai_rata'] > 0,
    'berkas' => !array_diff($wajib, array_keys($berkasMap)),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$bisaUbah) throw new RuntimeException('Formulir tidak bisa diubah lagi.');
        $aksi = $_POST['aksi'] ?? '';
        if ($aksi === 'biodata') {
            if (!preg_match('/^\d{16}$/', $_POST['nik'])) throw new RuntimeException('NIK harus 16 digit.');
            q('UPDATE pendaftar SET nik=?, jk=?, tempat_lahir=?, agama=?, asal_sekolah=?, alamat=?, jarak_km=? WHERE id=?', [
                $_POST['nik'], $_POST['jk'] === 'P' ? 'P' : 'L', trim($_POST['tempat_lahir']), trim($_POST['agama']), trim($_POST['asal_sekolah']), trim($_POST['alamat']), max(0, (float) $_POST['jarak_km']), $p['id']]);
            $next = 'ortu';
        } elseif ($aksi === 'ortu') {
            q('UPDATE pendaftar SET nama_ayah=?, nama_ibu=?, pekerjaan_ortu=?, telepon=? WHERE id=?', [trim($_POST['nama_ayah']), trim($_POST['nama_ibu']), trim($_POST['pekerjaan_ortu']), trim($_POST['telepon']), $p['id']]);
            $next = 'nilai';
        } elseif ($aksi === 'nilai') {
            $nilai = (float) $_POST['nilai_rata'];
            if ($nilai < 0 || $nilai > 100) throw new RuntimeException('Nilai rata-rata harus antara 0 dan 100.');
            q('UPDATE pendaftar SET nilai_rata=?, poin_prestasi=?, prestasi_ket=? WHERE id=?', [$nilai, $p['jalur'] === 'Prestasi' ? min(20, max(0, (float) $_POST['poin_prestasi'])) : 0, trim($_POST['prestasi_ket'] ?? ''), $p['id']]);
            $next = 'berkas';
        } elseif ($aksi === 'berkas') {
            $jenis = $_POST['jenis'] ?? '';
            if (!isset(BERKAS[$jenis]) || empty($_FILES['file']['tmp_name'])) throw new RuntimeException('Pilih jenis dan file berkas.');
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['file']['tmp_name']);
            $ext = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'][$mime] ?? null;
            if (!$ext || $_FILES['file']['size'] > 2 * 1024 * 1024) throw new RuntimeException('Berkas harus PDF/JPG/PNG maksimal 2 MB.');
            $nama = $p['id'] . '_' . $jenis . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/../../berkas/' . $nama);
            $lama = $berkasMap[$jenis]['file'] ?? null;
            q('INSERT INTO berkas (pendaftar_id, jenis, file) VALUES (?,?,?) ON DUPLICATE KEY UPDATE file = VALUES(file), uploaded_at = NOW()', [$p['id'], $jenis, $nama]);
            if ($lama && !str_starts_with($lama, 'contoh-')) @unlink(__DIR__ . '/../../berkas/' . $lama);
            $next = 'berkas';
        } elseif ($aksi === 'kirim') {
            if (in_array(false, $lengkap, true)) throw new RuntimeException('Masih ada langkah yang belum lengkap.');
            q("UPDATE pendaftar SET status = 'menunggu', dikirim_at = NOW(), catatan = NULL WHERE id = ?", [$p['id']]);
            flash('Formulir terkirim. Panitia akan memverifikasi berkasmu.');
            redirect('status');
        }
        flash('Tersimpan.');
        redirect('formulir', ['tab' => $next ?? $tab]);
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'error');
        redirect('formulir', ['tab' => $tab]);
    }
}
$dis = $bisaUbah ? '' : 'disabled';
$title = 'Formulir';
require __DIR__ . '/../../includes/pub_header.php';
?>
<div class="page-title"><div><h1><?= e($p['nama']) ?></h1><p><span class="mono"><?= e($p['no_daftar']) ?></span> · Jalur <?= e($p['jalur']) ?> · <?= badge($p['status']) ?></p></div>
    <div class="page-actions"><a class="btn" href="<?= url('status') ?>">Lihat status</a></div></div>
<?php if ($p['status'] === 'perbaikan'): ?><div class="callout red"><?= icon('alert', 18) ?><div><b>Perlu perbaikan:</b> <?= e($p['catatan']) ?>. Perbaiki lalu kirim ulang.</div></div><?php endif; ?>
<?php if (!$bisaUbah && $p['status'] !== 'perbaikan'): ?><div class="callout"><?= icon('lock', 18) ?><div>Formulir sudah dikirim atau pendaftaran sudah ditutup, data tidak bisa diubah.</div></div><?php endif; ?>
<nav class="steps">
    <?php foreach (['biodata' => 'Biodata', 'ortu' => 'Orang tua', 'nilai' => 'Nilai & prestasi', 'berkas' => 'Berkas'] as $k => $l): $i = array_search($k, array_keys($lengkap)) + 1; ?>
        <a href="<?= url('formulir', ['tab' => $k]) ?>" class="<?= $tab === $k ? 'on' : '' ?> <?= $lengkap[$k] ? 'done' : '' ?>"><span class="dot"><?= $lengkap[$k] ? '✓' : $i ?></span><?= $l ?></a>
    <?php endforeach; ?>
</nav>
<form method="post" enctype="multipart/form-data" class="box"><div class="box-body">
    <?= csrf_field() ?><input type="hidden" name="aksi" value="<?= e($tab) ?>">
    <?php if ($tab === 'biodata'): ?>
        <div class="form-grid">
            <div><label>NISN</label><input type="text" value="<?= e($p['nisn']) ?>" disabled></div>
            <div><label>NIK</label><input type="text" name="nik" maxlength="16" value="<?= e($p['nik']) ?>" required <?= $dis ?>></div>
            <div><label>Tempat lahir</label><input type="text" name="tempat_lahir" value="<?= e($p['tempat_lahir']) ?>" required <?= $dis ?>></div>
            <div><label>Tanggal lahir</label><input type="text" value="<?= tgl($p['tgl_lahir']) ?>" disabled></div>
            <div><label>Jenis kelamin</label><select name="jk" <?= $dis ?>><option value="L">Laki-laki</option><option value="P" <?= $p['jk'] === 'P' ? 'selected' : '' ?>>Perempuan</option></select></div>
            <div><label>Agama</label><select name="agama" <?= $dis ?>><?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $a): ?><option <?= $p['agama'] === $a ? 'selected' : '' ?>><?= $a ?></option><?php endforeach; ?></select></div>
            <div class="full"><label>Asal sekolah</label><input type="text" name="asal_sekolah" value="<?= e($p['asal_sekolah']) ?>" required <?= $dis ?>></div>
            <div class="full"><label>Alamat rumah</label><input type="text" name="alamat" value="<?= e($p['alamat']) ?>" required <?= $dis ?>></div>
            <div><label>Jarak rumah ke sekolah (km)</label><input type="number" name="jarak_km" step="0.01" min="0" value="<?= e($p['jarak_km']) ?>" <?= $dis ?>><div class="hint">Cek di Google Maps. Dipakai untuk peringkat jalur zonasi.</div></div>
        </div>
    <?php elseif ($tab === 'ortu'): ?>
        <div class="form-grid">
            <div><label>Nama ayah</label><input type="text" name="nama_ayah" value="<?= e($p['nama_ayah']) ?>" required <?= $dis ?>></div>
            <div><label>Nama ibu</label><input type="text" name="nama_ibu" value="<?= e($p['nama_ibu']) ?>" required <?= $dis ?>></div>
            <div><label>Pekerjaan orang tua</label><input type="text" name="pekerjaan_ortu" value="<?= e($p['pekerjaan_ortu']) ?>" <?= $dis ?>></div>
            <div><label>No. WhatsApp</label><input type="text" name="telepon" value="<?= e($p['telepon']) ?>" required <?= $dis ?>></div>
        </div>
    <?php elseif ($tab === 'nilai'): ?>
        <div class="form-grid">
            <div><label>Rata-rata nilai rapor (kelas 4–6)</label><input type="number" name="nilai_rata" step="0.01" min="0" max="100" value="<?= e($p['nilai_rata']) ?>" required <?= $dis ?>></div>
            <?php if ($p['jalur'] === 'Prestasi'): ?>
                <div><label>Poin prestasi (0–20)</label><input type="number" name="poin_prestasi" step="0.5" min="0" max="20" value="<?= e($p['poin_prestasi']) ?>" <?= $dis ?>><div class="hint">Juara kabupaten 5, provinsi 10, nasional 15 (diverifikasi panitia).</div></div>
                <div class="full"><label>Keterangan prestasi</label><input type="text" name="prestasi_ket" value="<?= e($p['prestasi_ket']) ?>" <?= $dis ?>></div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="files">
            <?php foreach (BERKAS as $k => $l): if ($k === 'pendukung' && $p['jalur'] === 'Zonasi') continue; $ada = $berkasMap[$k] ?? null; ?>
                <div class="file <?= $ada ? 'ok' : '' ?>"><?= icon('file', 18) ?><div class="grow"><strong style="color:var(--ink-2)"><?= $l ?></strong><div class="muted" style="font-size:12.5px"><?= $ada ? 'Terunggah ' . tgl($ada['uploaded_at']) . ' · <a href="' . url('berkas', ['id' => $ada['id']]) . '" target="_blank">lihat</a>' : 'Belum diunggah' ?></div></div>
                    <?= $ada ? '<span class="badge badge-green">Terunggah</span>' : '<span class="badge badge-gray">Wajib</span>' ?></div>
            <?php endforeach; ?>
        </div>
        <?php if ($bisaUbah): ?><div class="form-grid mt" style="margin-top:18px"><div><label>Jenis berkas</label><select name="jenis"><?php foreach (BERKAS as $k => $l): if ($k === 'pendukung' && $p['jalur'] === 'Zonasi') continue; ?><option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?></select></div>
            <div><label>File (PDF/JPG/PNG, maks 2 MB)</label><input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required></div></div><?php endif; ?>
    <?php endif; ?>
    <?php if ($bisaUbah): ?><div class="form-actions"><button class="btn btn-primary"><?= $tab === 'berkas' ? icon('upload', 15) . ' Unggah' : 'Simpan & lanjut' ?></button></div><?php endif; ?>
</div></form>
<?php if ($bisaUbah): ?>
<form method="post" class="box mt" data-confirm="Kirim formulir sekarang? Setelah dikirim data tidak bisa diubah."><div class="box-body" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
    <?= csrf_field() ?><input type="hidden" name="aksi" value="kirim">
    <div><strong style="color:var(--ink-2)">Kirim formulir ke panitia</strong><div class="muted" style="font-size:13.5px">Pastikan semua data benar. Setelah dikirim, data tidak bisa diubah kecuali panitia meminta perbaikan.</div></div>
    <button class="btn btn-primary btn-lg" <?= in_array(false, $lengkap, true) ? 'disabled title="Lengkapi semua langkah dulu"' : '' ?>>Kirim formulir</button>
</div></form>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/pub_footer.php'; ?>
