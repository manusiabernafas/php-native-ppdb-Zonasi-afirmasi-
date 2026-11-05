<?php
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['aksi'] ?? '') {
            case 'jadwal':
                $b = date('Y-m-d H:i:s', strtotime($_POST['buka'])); $t = date('Y-m-d H:i:s', strtotime($_POST['tutup'])); $u = date('Y-m-d H:i:s', strtotime($_POST['pengumuman']));
                if (!($b < $t && $t < $u)) throw new RuntimeException('Urutan jadwal harus: buka < tutup < pengumuman.');
                q('UPDATE pengaturan SET nama_sekolah=?, alamat=?, telepon=?, tahun_ajaran=?, buka=?, tutup=?, pengumuman=?, daftar_ulang_sampai=?, persen_cadangan=? WHERE id=1', [
                    trim($_POST['nama_sekolah']), trim($_POST['alamat']), trim($_POST['telepon']), trim($_POST['tahun_ajaran']), $b, $t, $u, $_POST['daftar_ulang_sampai'], min(50, max(0, (int) $_POST['persen_cadangan']))]);
                break;
            case 'kuota':
                foreach ($_POST['kuota'] ?? [] as $id => $k) q('UPDATE jalur SET kuota = ?, keterangan = ? WHERE id = ?', [max(0, (int) $k), trim($_POST['ket'][$id] ?? ''), (int) $id]);
                break;
            case 'user':
                if (strlen($_POST['password']) < 6) throw new RuntimeException('Password minimal 6 karakter.');
                q('INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)', [trim($_POST['nama']), trim($_POST['username']), password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['role'] === 'admin' ? 'admin' : 'verifikator']);
                break;
        }
        flash('Disimpan.');
    } catch (PDOException $e) {
        flash('Username sudah dipakai.', 'error');
    } catch (RuntimeException $e) {
        flash($e->getMessage(), 'error');
    }
    redirect('pengaturan');
}
$s = setting();
$jalur = q('SELECT * FROM jalur ORDER BY id')->fetchAll();
$users = q('SELECT * FROM users ORDER BY id')->fetchAll();
$dt = fn ($v) => date('Y-m-d\TH:i', strtotime($v));
$title = 'Jadwal & kuota';
$titleIcon = 'settings';
require __DIR__ . '/../../includes/header.php';
?>
<form method="post" class="box"><?= csrf_field() ?><input type="hidden" name="aksi" value="jadwal">
    <div class="box-head"><h2>Sekolah & jadwal</h2></div>
    <div class="box-body"><div class="form-grid">
        <div><label>Nama sekolah</label><input type="text" name="nama_sekolah" value="<?= e($s['nama_sekolah']) ?>"></div><div><label>Tahun ajaran</label><input type="text" name="tahun_ajaran" value="<?= e($s['tahun_ajaran']) ?>"></div>
        <div><label>Alamat</label><input type="text" name="alamat" value="<?= e($s['alamat']) ?>"></div><div><label>Telepon</label><input type="text" name="telepon" value="<?= e($s['telepon']) ?>"></div>
        <div><label>Pendaftaran dibuka</label><input type="datetime-local" name="buka" value="<?= $dt($s['buka']) ?>"></div><div><label>Pendaftaran ditutup</label><input type="datetime-local" name="tutup" value="<?= $dt($s['tutup']) ?>"></div>
        <div><label>Pengumuman</label><input type="datetime-local" name="pengumuman" value="<?= $dt($s['pengumuman']) ?>"></div><div><label>Batas daftar ulang</label><input type="date" name="daftar_ulang_sampai" value="<?= e($s['daftar_ulang_sampai']) ?>"></div>
        <div><label>Cadangan (% dari kuota)</label><input type="number" name="persen_cadangan" value="<?= (int) $s['persen_cadangan'] ?>" min="0" max="50"></div>
    </div><div class="form-actions"><button class="btn btn-primary">Simpan</button></div></div>
</form>
<div class="grid-2 mt">
    <form method="post" class="box"><?= csrf_field() ?><input type="hidden" name="aksi" value="kuota">
        <div class="box-head"><h2>Kuota jalur</h2><span class="muted">Total <?= array_sum(array_column($jalur, 'kuota')) ?></span></div>
        <div class="table-wrap"><table><tbody><?php foreach ($jalur as $j): ?><tr><td style="width:150px"><strong><?= e($j['nama']) ?></strong></td><td style="width:100px"><input type="number" name="kuota[<?= $j['id'] ?>]" value="<?= $j['kuota'] ?>" min="0"></td><td><input type="text" name="ket[<?= $j['id'] ?>]" value="<?= e($j['keterangan']) ?>"></td></tr><?php endforeach; ?></tbody></table></div>
        <div class="box-body"><button class="btn btn-primary">Simpan kuota</button></div>
    </form>
    <div class="box"><div class="box-head"><h2>Panitia</h2></div>
        <div class="table-wrap"><table><tbody><?php foreach ($users as $u): ?><tr><td><strong><?= e($u['nama']) ?></strong><small><?= e($u['username']) ?></small></td><td class="text-right"><span class="badge badge-gray"><?= e($u['role']) ?></span></td></tr><?php endforeach; ?></tbody></table></div>
        <form method="post" class="box-body filters"><?= csrf_field() ?><input type="hidden" name="aksi" value="user"><input type="text" name="nama" placeholder="Nama" required><input type="text" name="username" placeholder="Username" required><input type="password" name="password" placeholder="Password" minlength="6" required><select name="role"><option value="verifikator">Verifikator</option><option value="admin">Admin</option></select><button class="btn">Tambah</button></form>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
