<?php
$p = pendaftar();
if (!$p) redirect('masuk');
$set = setting();
$tampilHasil = tahap() === 'pengumuman' && in_array($p['status'], ['diterima', 'cadangan', 'tidak_diterima', 'daftar_ulang'], true);
$status = $tampilHasil ? $p['status'] : (in_array($p['status'], ['diterima', 'cadangan', 'tidak_diterima'], true) ? 'terverifikasi' : $p['status']);
$langkah = [['Akun dibuat', true, $p['created_at']], ['Formulir dikirim', (bool) $p['dikirim_at'], $p['dikirim_at']],
    ['Berkas diverifikasi', in_array($status, ['terverifikasi', 'diterima', 'cadangan', 'tidak_diterima', 'daftar_ulang'], true), $p['diverifikasi_at']],
    ['Pengumuman', $tampilHasil, $tampilHasil ? $set['pengumuman'] : null]];
$title = 'Status Pendaftaran';
require __DIR__ . '/../../includes/pub_header.php';
?>
<div class="page-title"><div><h1>Halo, <?= e(explode(' ', $p['nama'])[0]) ?></h1><p><span class="mono"><?= e($p['no_daftar']) ?></span> · Jalur <?= e($p['jalur']) ?></p></div>
    <div class="page-actions"><a class="btn" href="<?= url('formulir') ?>">Formulir</a><?php if ($p['status'] !== 'draft'): ?><a class="btn btn-primary" href="<?= url('kartu') ?>" target="_blank">Cetak kartu pendaftaran</a><?php endif; ?></div></div>
<?php
$pesan = [
    'draft' => ['amber', 'Formulir belum dikirim. Lengkapi semua langkah lalu klik Kirim formulir.'],
    'menunggu' => ['blue', 'Formulir sudah diterima dan sedang menunggu verifikasi panitia.'],
    'perbaikan' => ['red', 'Panitia meminta perbaikan: ' . ($p['catatan'] ?? '-') . '. Buka formulir, perbaiki, lalu kirim ulang.'],
    'terverifikasi' => ['blue', 'Berkas sudah diverifikasi. Tunggu pengumuman pada ' . tgl($set['pengumuman']) . ' pukul ' . date('H.i', strtotime($set['pengumuman'])) . '.'],
    'diterima' => ['green', 'Selamat! Kamu DITERIMA. Lakukan daftar ulang paling lambat ' . tgl($set['daftar_ulang_sampai']) . '.'],
    'cadangan' => ['amber', 'Kamu masuk daftar CADANGAN. Panitia akan menghubungi bila ada kursi kosong.'],
    'tidak_diterima' => ['', 'Mohon maaf, kamu belum diterima di jalur ini. Terima kasih telah mendaftar.'],
    'daftar_ulang' => ['green', 'Daftar ulang sudah tercatat. Sampai jumpa di sekolah!'],
][$status];
?>
<div class="callout <?= $pesan[0] ?>"><?= icon('megaphone', 18) ?><div><?= badge($status) ?> <?= e($pesan[1]) ?></div></div>
<div class="grid-2">
    <div class="box"><div class="box-head"><h2>Progres</h2></div><div class="box-body"><ul class="timeline">
        <?php foreach ($langkah as [$l, $ok, $w]): ?><li class="<?= $ok ? '' : 'muted' ?>"><span><?= $ok ? icon('check', 15) : '·' ?> <?= $w ? tgl($w) : '' ?></span><span><?= $l ?></span></li><?php endforeach; ?>
    </ul></div></div>
    <div class="box"><div class="box-head"><h2>Data utama</h2></div><div class="box-body"><dl class="props">
        <dt>NISN</dt><dd class="mono"><?= e($p['nisn']) ?></dd><dt>Asal sekolah</dt><dd><?= e($p['asal_sekolah'] ?: '-') ?></dd>
        <dt>Nilai rapor</dt><dd><?= e($p['nilai_rata']) ?></dd><?php if ($p['jalur'] === 'Zonasi'): ?><dt>Jarak</dt><dd><?= e($p['jarak_km']) ?> km</dd><?php endif; ?>
        <?php if ($tampilHasil && $p['peringkat']): ?><dt>Peringkat</dt><dd><b><?= $p['peringkat'] ?></b> (skor <?= e($p['skor']) ?>)</dd><?php endif; ?>
    </dl></div></div>
</div>
<?php require __DIR__ . '/../../includes/pub_footer.php'; ?>
