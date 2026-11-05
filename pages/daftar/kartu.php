<?php
$p = user() && isset($_GET['id']) ? q('SELECT p.*, j.nama jalur FROM pendaftar p JOIN jalur j ON j.id = p.jalur_id WHERE p.id = ?', [(int) $_GET['id']])->fetch() : pendaftar();
if (!$p || $p['status'] === 'draft') redirect('beranda');
$foto = q("SELECT id FROM berkas WHERE pendaftar_id = ? AND jenis = 'foto'", [$p['id']])->fetchColumn();
$set = setting();
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Kartu <?= e($p['no_daftar']) ?></title><link rel="stylesheet" href="assets/css/style.css"></head>
<body style="padding:30px" onload="window.print()">
<div class="card-print">
    <div class="head"><div><b style="font-size:17px">KARTU PENDAFTARAN PPDB <?= e($set['tahun_ajaran']) ?></b><div><?= e($set['nama_sekolah']) ?></div></div><div class="mono" style="font-size:18px;font-weight:700"><?= e($p['no_daftar']) ?></div></div>
    <div style="display:flex;gap:20px">
        <?php if ($foto): ?><img src="<?= url('berkas', ['id' => $foto]) ?>" alt="Foto"><?php endif; ?>
        <dl class="props" style="flex:1;grid-template-columns:130px 1fr">
            <dt>Nama</dt><dd><b><?= e($p['nama']) ?></b></dd><dt>NISN</dt><dd><?= e($p['nisn']) ?></dd><dt>TTL</dt><dd><?= e($p['tempat_lahir']) ?>, <?= tgl($p['tgl_lahir']) ?></dd>
            <dt>Asal sekolah</dt><dd><?= e($p['asal_sekolah']) ?></dd><dt>Jalur</dt><dd><?= e($p['jalur']) ?></dd><dt>Dikirim</dt><dd><?= tgl($p['dikirim_at']) ?></dd>
        </dl>
    </div>
    <p class="muted" style="font-size:12.5px;margin:16px 0 0">Simpan kartu ini. Bawa saat daftar ulang bila diterima. Pengumuman: <?= tgl($set['pengumuman']) ?>.</p>
</div>
</body></html>
