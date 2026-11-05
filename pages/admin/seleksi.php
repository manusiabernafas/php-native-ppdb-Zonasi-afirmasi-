<?php
$set = setting();
$jalur = q('SELECT * FROM jalur ORDER BY id')->fetchAll();
$admin = user()['role'] === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $admin) {
    // Proses seleksi: peringkat ulang SEMUA pendaftar terverifikasi di setiap jalur, lalu tetapkan status sesuai kuota
    db()->beginTransaction();
    $ringkas = [];
    foreach ($jalur as $j) {
        $calon = q("SELECT * FROM pendaftar WHERE jalur_id = ? AND status IN ('terverifikasi','diterima','cadangan','tidak_diterima')", [$j['id']])->fetchAll();
        foreach ($calon as &$c) $c['skor'] = hitung_skor($c, $j['kode']);
        unset($c);
        // Urutan: skor tertinggi; bila sama → (zonasi) jarak terdekat, (lainnya) nilai rapor tertinggi; lalu yang lebih dulu mengirim
        usort($calon, fn ($a, $b) => [$b['skor'], $j['kode'] === 'zonasi' ? -$a['jarak_km'] : $a['nilai_rata'], $b['dikirim_at']]
            <=> [$a['skor'], $j['kode'] === 'zonasi' ? -$b['jarak_km'] : $b['nilai_rata'], $a['dikirim_at']]);
        $kuotaCadangan = (int) ceil($j['kuota'] * $set['persen_cadangan'] / 100);
        foreach ($calon as $i => $c) {
            $status = $i < $j['kuota'] ? 'diterima' : ($i < $j['kuota'] + $kuotaCadangan ? 'cadangan' : 'tidak_diterima');
            q('UPDATE pendaftar SET skor = ?, peringkat = ?, status = ? WHERE id = ?', [$c['skor'], $i + 1, $status, $c['id']]);
        }
        $ringkas[] = $j['nama'] . ' ' . min(count($calon), $j['kuota']) . '/' . $j['kuota'];
    }
    db()->commit();
    flash('Seleksi selesai: ' . implode(', ', $ringkas) . '. Hasil tampil ke calon siswa mulai ' . tgl($set['pengumuman']) . '.');
    redirect('seleksi', ['jalur' => $_GET['jalur'] ?? $jalur[0]['id']]);
}

$jid = (int) ($_GET['jalur'] ?? $jalur[0]['id']);
$aktif = array_values(array_filter($jalur, fn ($j) => (int) $j['id'] === $jid))[0] ?? $jalur[0];
$hasil = q("SELECT * FROM pendaftar WHERE jalur_id = ? AND status IN ('diterima','cadangan','tidak_diterima','daftar_ulang') ORDER BY peringkat", [$aktif['id']])->fetchAll();
$belum = (int) q("SELECT COUNT(*) FROM pendaftar WHERE status IN ('menunggu','perbaikan')")->fetchColumn();
$hit = array_count_values(array_column($hasil, 'status'));

$title = 'Seleksi & pengumuman';
$titleIcon = 'award';
$subtitle = 'Pengumuman tampil ke calon siswa: <b>' . tgl($set['pengumuman']) . ' ' . date('H.i', strtotime($set['pengumuman'])) . '</b> · cadangan ' . $set['persen_cadangan'] . '% dari kuota';
$actions = $admin ? '<form method="post" data-confirm="Jalankan seleksi untuk semua jalur? Peringkat akan dihitung ulang."><input type="hidden" name="_csrf" value="' . csrf_token() . '"><button class="btn btn-primary">' . icon('award', 15) . ' Jalankan seleksi</button></form>' : '';
$wide = true;
require __DIR__ . '/../../includes/header.php';
?>
<?php if ($belum): ?><div class="callout amber"><?= icon('alert', 18) ?><div>Masih ada <b><?= $belum ?></b> pendaftar menunggu verifikasi / perbaikan. Mereka belum ikut diseleksi.</div></div><?php endif; ?>
<?php if (tahap() === 'pendaftaran'): ?><div class="callout blue"><?= icon('clock', 18) ?><div>Pendaftaran masih dibuka. Seleksi bisa dijalankan sebagai simulasi dan diulang kapan saja; hasil hanya terlihat calon siswa setelah jam pengumuman.</div></div><?php endif; ?>
<nav class="steps">
    <?php foreach ($jalur as $j): ?><a href="<?= url('seleksi', ['jalur' => $j['id']]) ?>" class="<?= (int) $j['id'] === (int) $aktif['id'] ? 'on' : '' ?>"><?= icon('route', 15) ?><?= e($j['nama']) ?> <span class="muted">(<?= $j['kuota'] ?>)</span></a><?php endforeach; ?>
</nav>
<div class="stats">
    <div class="stat"><small>Kuota</small><strong><?= $aktif['kuota'] ?></strong><span><?= e($aktif['keterangan']) ?></span></div>
    <div class="stat"><small>Diterima</small><strong><?= ($hit['diterima'] ?? 0) + ($hit['daftar_ulang'] ?? 0) ?></strong><span><?= $hit['daftar_ulang'] ?? 0 ?> sudah daftar ulang</span></div>
    <div class="stat"><small>Cadangan</small><strong><?= $hit['cadangan'] ?? 0 ?></strong></div>
    <div class="stat"><small>Tidak diterima</small><strong><?= $hit['tidak_diterima'] ?? 0 ?></strong></div>
</div>
<div class="box">
    <div class="box-head"><h2>Peringkat jalur <?= e($aktif['nama']) ?></h2><span class="muted"><?= $aktif['kode'] === 'zonasi' ? 'Skor = 100 − jarak×10 + 10% nilai rapor' : ($aktif['kode'] === 'prestasi' ? 'Skor = nilai rapor + poin prestasi' : 'Skor = nilai rapor') ?></span></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Nama</th><th>Asal sekolah</th><th>Rapor</th><?= $aktif['kode'] === 'zonasi' ? '<th>Jarak</th>' : '' ?><?= $aktif['kode'] === 'prestasi' ? '<th>Poin</th>' : '' ?><th>Skor</th><th>Hasil</th></tr></thead>
        <tbody><?php foreach ($hasil as $h): ?>
            <tr onclick="location.href='<?= url('verifikasi', ['id' => $h['id']]) ?>'" style="cursor:pointer;<?= $h['peringkat'] == $aktif['kuota'] ? 'border-bottom:2px solid var(--accent)' : '' ?>">
                <td><b><?= $h['peringkat'] ?></b></td><td><strong><?= e($h['nama']) ?></strong><small class="mono"><?= e($h['no_daftar']) ?></small></td><td><?= e($h['asal_sekolah']) ?></td><td><?= e($h['nilai_rata']) ?></td>
                <?= $aktif['kode'] === 'zonasi' ? '<td>' . e($h['jarak_km']) . ' km</td>' : '' ?><?= $aktif['kode'] === 'prestasi' ? '<td>' . e($h['poin_prestasi']) . '</td>' : '' ?>
                <td><b><?= e($h['skor']) ?></b></td><td><?= badge($h['status']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$hasil): ?><tr><td colspan="8" class="empty">Belum ada hasil seleksi. Klik "Jalankan seleksi".</td></tr><?php endif; ?></tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
