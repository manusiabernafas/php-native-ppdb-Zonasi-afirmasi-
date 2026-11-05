<?php
$set = setting();
$hit = q('SELECT status, COUNT(*) n FROM pendaftar GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$jalur = q("SELECT j.*, COUNT(p.id) daftar, SUM(p.status = 'terverifikasi' OR p.status IN ('diterima','cadangan','tidak_diterima','daftar_ulang')) verif FROM jalur j LEFT JOIN pendaftar p ON p.jalur_id = j.id AND p.status <> 'draft' GROUP BY j.id ORDER BY j.id")->fetchAll();
$harian = [];
for ($i = 13; $i >= 0; $i--) $harian[date('Y-m-d', strtotime("-$i day"))] = 0;
foreach (q('SELECT DATE(dikirim_at) d, COUNT(*) n FROM pendaftar WHERE dikirim_at >= CURDATE() - INTERVAL 13 DAY GROUP BY d')->fetchAll() as $r) $harian[$r['d']] = (int) $r['n'];
$maxH = max(1, ...array_values($harian));
$sekolah = q("SELECT asal_sekolah, COUNT(*) n FROM pendaftar WHERE status <> 'draft' GROUP BY asal_sekolah ORDER BY n DESC LIMIT 6")->fetchAll();
$tahap = tahap();
$title = 'Ringkasan';
$subtitle = 'Tahap saat ini: <b>' . ['belum' => 'Belum dibuka', 'pendaftaran' => 'Pendaftaran dibuka', 'seleksi' => 'Verifikasi & seleksi', 'pengumuman' => 'Pengumuman'][$tahap] . '</b> · tutup ' . tgl($set['tutup']) . ' · pengumuman ' . tgl($set['pengumuman']);
$actions = '<a class="btn btn-primary" href="index.php?page=pendaftar&status=menunggu">' . icon('file', 15) . ' Verifikasi berkas (' . ($hit['menunggu'] ?? 0) . ')</a>';
require __DIR__ . '/../../includes/header.php';
?>
<div class="stats">
    <div class="stat"><small>Total pendaftar</small><strong><?= array_sum($hit) - ($hit['draft'] ?? 0) ?></strong><span>+<?= $hit['draft'] ?? 0 ?> belum mengirim formulir</span></div>
    <div class="stat"><small>Menunggu verifikasi</small><strong><?= $hit['menunggu'] ?? 0 ?></strong><span><?= $hit['perbaikan'] ?? 0 ?> diminta perbaikan</span></div>
    <div class="stat"><small>Terverifikasi</small><strong><?= ($hit['terverifikasi'] ?? 0) + ($hit['diterima'] ?? 0) + ($hit['cadangan'] ?? 0) + ($hit['tidak_diterima'] ?? 0) + ($hit['daftar_ulang'] ?? 0) ?></strong><span>siap diseleksi</span></div>
    <div class="stat"><small>Total kuota</small><strong><?= array_sum(array_column($jalur, 'kuota')) ?></strong><span>kursi di <?= count($jalur) ?> jalur</span></div>
</div>
<div class="grid-2">
    <div class="box"><div class="box-head"><h2>Pendaftar vs kuota per jalur</h2></div><div class="box-body">
        <?php foreach ($jalur as $j): $pct = min(100, round($j['daftar'] / max(1, $j['kuota']) * 100)); ?>
            <div class="quota"><div class="row"><span><b><?= e($j['nama']) ?></b> <span class="muted">· <?= (int) $j['verif'] ?> terverifikasi</span></span><span><?= $j['daftar'] ?> / <?= $j['kuota'] ?></span></div>
                <div class="track"><div class="fill <?= $j['daftar'] > $j['kuota'] ? 'over' : '' ?>" style="width:<?= $pct ?>%"></div></div></div>
        <?php endforeach; ?>
        <p class="muted" style="font-size:12.5px;margin:6px 0 0">Kuning: pendaftar melebihi kuota, seleksi berdasarkan peringkat.</p>
    </div></div>
    <div class="box"><div class="box-head"><h2>Formulir masuk 14 hari</h2></div><div class="box-body"><div class="bars">
        <?php foreach ($harian as $d => $n): ?><div class="bar-col" title="<?= tgl($d) ?>: <?= $n ?>"><small><?= $n ?: '' ?></small><div class="bar" style="height:<?= round($n / $maxH * 100) ?>%"></div><small><?= date('d', strtotime($d)) ?></small></div><?php endforeach; ?>
    </div></div></div>
</div>
<div class="box mt"><div class="box-head"><h2>Asal sekolah terbanyak</h2></div>
    <div class="table-wrap"><table><tbody><?php foreach ($sekolah as $s): ?><tr><td><?= e($s['asal_sekolah'] ?: '(belum diisi)') ?></td><td class="text-right"><?= $s['n'] ?> pendaftar</td></tr><?php endforeach; ?></tbody></table></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
