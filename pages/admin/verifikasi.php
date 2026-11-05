<?php
$p = q('SELECT p.*, j.nama jalur, u.nama verifikator FROM pendaftar p JOIN jalur j ON j.id = p.jalur_id LEFT JOIN users u ON u.id = p.verifikator_id WHERE p.id = ?', [(int) ($_GET['id'] ?? 0)])->fetch();
if (!$p) redirect('pendaftar');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'daftar_ulang' && $p['status'] === 'diterima') {
        q("UPDATE pendaftar SET status = 'daftar_ulang' WHERE id = ?", [$p['id']]);
        flash('Daftar ulang dicatat.');
    } elseif (in_array($aksi, ['terverifikasi', 'perbaikan'], true) && in_array($p['status'], ['menunggu', 'terverifikasi', 'perbaikan'], true)) {
        $catatan = trim($_POST['catatan'] ?? '');
        if ($aksi === 'perbaikan' && $catatan === '') { flash('Tulis catatan perbaikan untuk calon siswa.', 'error'); redirect('verifikasi', ['id' => $p['id']]); }
        $poin = $p['jalur'] === 'Prestasi' ? min(20, max(0, (float) ($_POST['poin_prestasi'] ?? $p['poin_prestasi']))) : 0;
        q('UPDATE pendaftar SET status = ?, catatan = ?, poin_prestasi = ?, verifikator_id = ?, diverifikasi_at = NOW() WHERE id = ?', [$aksi, $aksi === 'perbaikan' ? $catatan : null, $poin, user()['id'], $p['id']]);
        flash($aksi === 'terverifikasi' ? "{$p['nama']} terverifikasi." : 'Permintaan perbaikan dikirim ke calon siswa.');
        // Lanjut ke pendaftar berikutnya yang menunggu
        $next = q("SELECT id FROM pendaftar WHERE status = 'menunggu' AND id <> ? ORDER BY dikirim_at LIMIT 1", [$p['id']])->fetchColumn();
        redirect($next ? 'verifikasi' : 'pendaftar', $next ? ['id' => $next] : ['status' => 'menunggu']);
    }
    redirect('verifikasi', ['id' => $p['id']]);
}
$berkas = q('SELECT * FROM berkas WHERE pendaftar_id = ?', [$p['id']])->fetchAll();
$title = $p['nama'];
$titleIcon = 'file';
$subtitle = '<span class="mono">' . e($p['no_daftar']) . '</span> · Jalur ' . e($p['jalur']) . ' · ' . badge($p['status']);
$actions = '<a class="btn" href="' . url('kartu', ['id' => $p['id']]) . '" target="_blank">Kartu</a>';
$wide = true;
require __DIR__ . '/../../includes/header.php';
?>
<div class="grid-2">
    <div>
        <div class="box"><div class="box-head"><h2>Data pendaftar</h2></div><div class="box-body"><dl class="props">
            <dt>NISN / NIK</dt><dd class="mono"><?= e($p['nisn']) ?> · <?= e($p['nik'] ?: '-') ?></dd>
            <dt>Tempat, tgl lahir</dt><dd><?= e($p['tempat_lahir']) ?>, <?= tgl($p['tgl_lahir']) ?></dd>
            <dt>Jenis kelamin</dt><dd><?= $p['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' ?> · <?= e($p['agama']) ?></dd>
            <dt>Asal sekolah</dt><dd><?= e($p['asal_sekolah']) ?></dd>
            <dt>Alamat</dt><dd><?= e($p['alamat']) ?></dd>
            <dt>Jarak ke sekolah</dt><dd><?= e($p['jarak_km']) ?> km</dd>
            <dt>Orang tua</dt><dd><?= e($p['nama_ayah']) ?> & <?= e($p['nama_ibu']) ?><br><span class="muted"><?= e($p['pekerjaan_ortu']) ?> · <?= e($p['telepon']) ?></span></dd>
            <dt>Nilai rapor</dt><dd><b><?= e($p['nilai_rata']) ?></b></dd>
            <?php if ($p['jalur'] === 'Prestasi'): ?><dt>Prestasi</dt><dd><?= e($p['prestasi_ket'] ?: '-') ?> <span class="muted">(<?= e($p['poin_prestasi']) ?> poin diajukan)</span></dd><?php endif; ?>
            <dt>Skor seleksi</dt><dd><?= $p['skor'] !== null ? e($p['skor']) . ' · peringkat ' . $p['peringkat'] : '<span class="muted">belum diseleksi</span>' ?></dd>
        </dl></div></div>
        <div class="box mt"><div class="box-head"><h2>Berkas</h2></div><div class="box-body files">
            <?php foreach (BERKAS as $k => $l): if ($k === 'pendukung' && $p['jalur'] === 'Zonasi') continue; $b = array_values(array_filter($berkas, fn ($x) => $x['jenis'] === $k))[0] ?? null; ?>
                <div class="file <?= $b ? 'ok' : '' ?>"><?= icon('file', 18) ?><div class="grow"><strong style="color:var(--ink-2)"><?= $l ?></strong><div class="muted" style="font-size:12.5px"><?= $b ? 'Diunggah ' . tgl($b['uploaded_at']) : 'Tidak ada' ?></div></div>
                    <?php if ($b): ?><a class="btn" href="<?= url('berkas', ['id' => $b['id']]) ?>" target="_blank">Buka</a><?php else: ?><span class="badge badge-red">Kosong</span><?php endif; ?></div>
            <?php endforeach; ?>
        </div></div>
    </div>
    <div>
        <?php if (in_array($p['status'], ['menunggu', 'terverifikasi', 'perbaikan'], true)): ?>
            <form method="post" class="box"><?= csrf_field() ?>
                <div class="box-head"><h2>Keputusan verifikasi</h2></div>
                <div class="box-body">
                    <?php if ($p['jalur'] === 'Prestasi'): ?><label>Poin prestasi disetujui</label><input type="number" name="poin_prestasi" step="0.5" min="0" max="20" value="<?= e($p['poin_prestasi']) ?>" style="margin-bottom:12px"><?php endif; ?>
                    <label>Catatan untuk calon siswa (wajib bila minta perbaikan)</label>
                    <textarea name="catatan" rows="3" placeholder="Contoh: Foto KK buram, mohon unggah ulang"><?= e($p['catatan']) ?></textarea>
                    <div class="form-actions"><button class="btn btn-primary" name="aksi" value="terverifikasi"><?= icon('check', 15) ?> Berkas valid</button><button class="btn" name="aksi" value="perbaikan">Minta perbaikan</button></div>
                    <p class="hint">Setelah menyimpan, otomatis lanjut ke pendaftar berikutnya yang menunggu.</p>
                </div>
            </form>
        <?php elseif ($p['status'] === 'diterima'): ?>
            <form method="post" class="box"><?= csrf_field() ?><div class="box-body"><div class="callout green" style="margin:0 0 12px"><?= icon('award', 18) ?><div>Diterima dengan peringkat <?= $p['peringkat'] ?>.</div></div>
                <button class="btn btn-primary btn-block" name="aksi" value="daftar_ulang">Catat daftar ulang</button></div></form>
        <?php else: ?>
            <div class="callout"><?= icon('file', 18) ?><div>Status: <?= badge($p['status']) ?></div></div>
        <?php endif; ?>
        <div class="box mt"><div class="box-head"><h2>Riwayat</h2></div><div class="box-body"><ul class="timeline">
            <li><span><?= tgl($p['created_at']) ?></span><span>Akun dibuat</span></li>
            <?php if ($p['dikirim_at']): ?><li><span><?= tgl($p['dikirim_at']) ?></span><span>Formulir dikirim</span></li><?php endif; ?>
            <?php if ($p['diverifikasi_at']): ?><li><span><?= tgl($p['diverifikasi_at']) ?></span><span>Diverifikasi oleh <?= e($p['verifikator'] ?? '-') ?></span></li><?php endif; ?>
        </ul></div></div>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
