<?php
$set = setting();
$terbuka = tahap() === 'pengumuman';
$hasil = null;
if ($terbuka && !empty($_GET['no'])) {
    $hasil = q("SELECT p.*, j.nama jalur FROM pendaftar p JOIN jalur j ON j.id = p.jalur_id WHERE p.no_daftar = ? AND p.status IN ('diterima','cadangan','tidak_diterima','daftar_ulang')", [strtoupper(trim($_GET['no']))])->fetch();
}
$title = 'Pengumuman';
require __DIR__ . '/../../includes/pub_header.php';
?>
<div class="page-title"><div><div class="icon-big"><?= icon('megaphone', 26) ?></div><h1>Pengumuman hasil seleksi</h1><p><?= e($set['nama_sekolah']) ?> · <?= e($set['tahun_ajaran']) ?></p></div></div>
<?php if (!$terbuka): ?>
    <div class="callout amber"><?= icon('clock', 18) ?><div>Pengumuman dibuka <b><?= tgl($set['pengumuman']) ?> pukul <?= date('H.i', strtotime($set['pengumuman'])) ?></b>.</div></div>
<?php else: ?>
    <form class="box" method="get" style="max-width:560px"><div class="box-body filters">
        <input type="hidden" name="page" value="pengumuman"><input type="text" name="no" class="mono" value="<?= e($_GET['no'] ?? '') ?>" placeholder="Nomor pendaftaran" required style="flex:1"><button class="btn btn-primary">Cek hasil</button>
    </div></form>
    <?php if (!empty($_GET['no']) && !$hasil): ?><div class="alert alert-error mt">Nomor tidak ditemukan atau belum masuk tahap seleksi.</div><?php endif; ?>
    <?php if ($hasil): ?>
        <div class="box mt" style="max-width:560px"><div class="box-body" style="text-align:center;padding:30px">
            <?= badge($hasil['status']) ?><h2 style="font-size:24px;margin-top:10px"><?= e($hasil['nama']) ?></h2>
            <p class="muted">Jalur <?= e($hasil['jalur']) ?><?= $hasil['peringkat'] ? ' · peringkat ' . $hasil['peringkat'] : '' ?></p>
            <?php if ($hasil['status'] === 'diterima'): ?><p>Silakan daftar ulang paling lambat <b><?= tgl($set['daftar_ulang_sampai']) ?></b>.</p><?php endif; ?>
        </div></div>
    <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/pub_footer.php'; ?>
