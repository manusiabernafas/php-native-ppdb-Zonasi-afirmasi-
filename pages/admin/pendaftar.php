<?php
$status = $_GET['status'] ?? '';
$jalurId = (int) ($_GET['jalur'] ?? 0);
$cari = trim($_GET['cari'] ?? '');
$w = ['1=1']; $prm = [];
if (isset(STATUS[$status])) { $w[] = 'p.status = ?'; $prm[] = $status; }
if ($jalurId) { $w[] = 'p.jalur_id = ?'; $prm[] = $jalurId; }
if ($cari !== '') { $w[] = '(p.no_daftar LIKE ? OR p.nama LIKE ? OR p.nisn LIKE ? OR p.asal_sekolah LIKE ?)'; array_push($prm, "%$cari%", "%$cari%", "%$cari%", "%$cari%"); }
$rows = q('SELECT p.*, j.nama jalur, (SELECT COUNT(*) FROM berkas b WHERE b.pendaftar_id = p.id) jml_berkas FROM pendaftar p JOIN jalur j ON j.id = p.jalur_id WHERE ' . implode(' AND ', $w)
    . " ORDER BY FIELD(p.status,'menunggu','perbaikan','terverifikasi'), p.dikirim_at LIMIT 400", $prm)->fetchAll();
$jalur = q('SELECT * FROM jalur ORDER BY id')->fetchAll();
$title = $status === 'menunggu' ? 'Perlu verifikasi' : 'Semua pendaftar';
$titleIcon = $status === 'menunggu' ? 'file' : 'users';
$subtitle = count($rows) . ' pendaftar ditampilkan';
$wide = true;
require __DIR__ . '/../../includes/header.php';
?>
<div class="box">
    <div class="box-head"><form class="filters" method="get"><input type="hidden" name="page" value="pendaftar">
        <select name="status" onchange="this.form.submit()"><option value="">Semua status</option><?php foreach (STATUS as $k => [$l]): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
        <select name="jalur" onchange="this.form.submit()"><option value="0">Semua jalur</option><?php foreach ($jalur as $j): ?><option value="<?= $j['id'] ?>" <?= $jalurId === (int) $j['id'] ? 'selected' : '' ?>><?= e($j['nama']) ?></option><?php endforeach; ?></select>
        <input type="search" name="cari" value="<?= e($cari) ?>" placeholder="No. daftar, nama, NISN, sekolah"></form></div>
    <div class="table-wrap"><table>
        <thead><tr><th>No. daftar</th><th>Nama</th><th>Jalur</th><th>Asal sekolah</th><th>Rapor</th><th>Jarak</th><th>Berkas</th><th>Dikirim</th><th>Status</th></tr></thead>
        <tbody><?php foreach ($rows as $r): ?>
            <tr onclick="location.href='<?= url('verifikasi', ['id' => $r['id']]) ?>'" style="cursor:pointer">
                <td class="mono"><?= e($r['no_daftar']) ?></td><td><strong><?= e($r['nama']) ?></strong><small>NISN <?= e($r['nisn']) ?></small></td><td><?= e($r['jalur']) ?></td>
                <td><?= e($r['asal_sekolah'] ?: '-') ?></td><td><?= e($r['nilai_rata']) ?><?= $r['poin_prestasi'] > 0 ? ' <small>+' . e($r['poin_prestasi']) . ' poin</small>' : '' ?></td><td><?= e($r['jarak_km']) ?> km</td>
                <td><?= $r['jml_berkas'] ?>/<?= $r['jalur'] === 'Zonasi' ? 4 : 5 ?></td><td><?= $r['dikirim_at'] ? tgl($r['dikirim_at']) : '-' ?></td><td><?= badge($r['status']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="9" class="empty">Tidak ada pendaftar.</td></tr><?php endif; ?></tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
