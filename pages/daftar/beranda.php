<?php
$set = setting();
$jalur = q("SELECT j.*, COUNT(p.id) n FROM jalur j LEFT JOIN pendaftar p ON p.jalur_id = j.id AND p.status <> 'draft' GROUP BY j.id ORDER BY j.id")->fetchAll();
$tahap = tahap();
$title = 'Informasi PPDB';
require __DIR__ . '/../../includes/pub_header.php';
?>
<div class="page-title" style="align-items:flex-start">
    <div><div class="icon-big"><?= icon('school', 26) ?></div>
        <h1>Penerimaan Peserta Didik Baru<br><?= e($set['nama_sekolah']) ?></h1>
        <p>Tahun ajaran <?= e($set['tahun_ajaran']) ?> · <?= e($set['alamat']) ?></p></div>
    <div class="page-actions"><?php if ($tahap === 'pendaftaran'): ?><a class="btn btn-primary btn-lg" href="<?= url('daftar') ?>">Daftar sekarang</a><a class="btn btn-lg" href="<?= url('masuk') ?>">Lanjutkan pendaftaran</a><?php else: ?><a class="btn btn-primary btn-lg" href="<?= url('pengumuman') ?>">Cek pengumuman</a><?php endif; ?></div>
</div>
<?php if ($tahap === 'pendaftaran'): ?>
    <div class="callout blue"><?= icon('megaphone', 18) ?><div>Pendaftaran <b>dibuka</b> sampai <?= tgl($set['tutup']) ?> pukul <?= date('H.i', strtotime($set['tutup'])) ?>. Siapkan scan KK, akta kelahiran, rapor, dan pas foto.</div></div>
<?php elseif ($tahap === 'belum'): ?>
    <div class="callout amber"><?= icon('clock', 18) ?><div>Pendaftaran dibuka <?= tgl($set['buka']) ?>.</div></div>
<?php else: ?>
    <div class="callout"><?= icon('clock', 18) ?><div>Pendaftaran sudah ditutup. Pengumuman hasil seleksi: <?= tgl($set['pengumuman']) ?> pukul <?= date('H.i', strtotime($set['pengumuman'])) ?>.</div></div>
<?php endif; ?>
<div class="grid-2">
    <div class="box">
        <div class="box-head"><h2>Jalur pendaftaran & kuota</h2><span class="muted">Total <?= array_sum(array_column($jalur, 'kuota')) ?> kursi</span></div>
        <div class="table-wrap"><table>
            <thead><tr><th>Jalur</th><th>Kuota</th><th>Pendaftar</th></tr></thead>
            <tbody><?php foreach ($jalur as $j): ?><tr><td><strong><?= e($j['nama']) ?></strong><small><?= e($j['keterangan']) ?></small></td><td><?= $j['kuota'] ?></td><td><?= $j['n'] ?></td></tr><?php endforeach; ?></tbody>
        </table></div>
    </div>
    <div class="box">
        <div class="box-head"><h2>Jadwal</h2></div>
        <div class="box-body"><ul class="timeline">
            <li class="<?= $tahap === 'pendaftaran' ? 'now' : '' ?>"><span><?= tgl($set['buka']) ?></span><span>Pendaftaran & unggah berkas dibuka</span></li>
            <li><span><?= tgl($set['tutup']) ?></span><span>Pendaftaran ditutup</span></li>
            <li class="<?= $tahap === 'seleksi' ? 'now' : '' ?>"><span>Setelah tutup</span><span>Verifikasi berkas & seleksi</span></li>
            <li class="<?= $tahap === 'pengumuman' ? 'now' : '' ?>"><span><?= tgl($set['pengumuman']) ?></span><span>Pengumuman hasil seleksi</span></li>
            <li><span>s/d <?= tgl($set['daftar_ulang_sampai']) ?></span><span>Daftar ulang siswa diterima</span></li>
        </ul></div>
    </div>
</div>
<div class="box mt"><div class="box-head"><h2>Alur pendaftaran</h2></div><div class="box-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px">
    <?php foreach ([['1', 'Buat akun', 'Pilih jalur, isi NISN & tanggal lahir. Simpan nomor pendaftaran.'], ['2', 'Lengkapi formulir', 'Biodata, data orang tua, nilai rapor, dan prestasi.'], ['3', 'Unggah berkas', 'KK, akta, rapor, pas foto, dan dokumen jalur.'], ['4', 'Kirim & pantau', 'Panitia memverifikasi. Cek status dan pengumuman.']] as [$n, $h, $d]): ?>
        <div><div class="badge badge-blue" style="margin-bottom:6px">Langkah <?= $n ?></div><strong style="display:block;color:var(--ink-2)"><?= $h ?></strong><span class="muted" style="font-size:13.5px"><?= $d ?></span></div>
    <?php endforeach; ?>
</div></div>
<p class="muted" style="margin-top:30px;font-size:13px">Panitia: <a href="<?= url('login') ?>">login di sini</a> · Informasi: <?= e($set['telepon']) ?></p>
<?php require __DIR__ . '/../../includes/pub_footer.php'; ?>
