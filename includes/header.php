<?php
$navHit = q('SELECT status, COUNT(*) n FROM pendaftar GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$nav = [
    'Panitia' => [['dashboard', 'Ringkasan', 'home', null], ['pendaftar', 'Semua pendaftar', 'users', array_sum($navHit)], ['pendaftar&status=menunggu', 'Perlu verifikasi', 'file', $navHit['menunggu'] ?? 0], ['seleksi', 'Seleksi & pengumuman', 'award', null]],
];
if (user()['role'] === 'admin') $nav['Sistem'] = [['pengaturan', 'Jadwal & kuota', 'settings', null]];
$current = $_GET['page'] ?? 'dashboard';
if ($current === 'pendaftar' && ($_GET['status'] ?? '') === 'menunggu') $current = 'pendaftar&status=menunggu';
if ($current === 'verifikasi') $current = 'pendaftar';
$flash = flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Ringkasan') ?> · Panitia PPDB</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="side" id="side">
        <div class="ws"><span class="logo"><?= icon('school', 14) ?></span><div>PPDB <?= e(setting()['tahun_ajaran']) ?><small><?= e(setting()['nama_sekolah']) ?></small></div></div>
        <?php foreach ($nav as $sec => $items): ?>
            <div class="sec"><?= $sec ?></div>
            <?php foreach ($items as [$key, $label, $ic, $n]): ?>
                <a href="index.php?page=<?= $key ?>" class="<?= $current === $key ? 'active' : '' ?>"><?= icon($ic, 16) ?> <?= $label ?><?php if ($n !== null): ?><span class="n"><?= $n ?></span><?php endif; ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <div class="sec">Publik</div>
        <a href="<?= url('beranda') ?>" target="_blank"><?= icon('external', 16) ?> Halaman pendaftaran</a>
        <div class="grow"></div>
        <a href="<?= url('logout') ?>"><?= icon('logout', 16) ?> Keluar · <?= e(user()['nama']) ?></a>
    </aside>
    <main class="main">
        <div class="crumbs"><button class="btn menu-mobile" style="display:none" onclick="document.getElementById('side').classList.toggle('open')"><?= icon('menu', 16) ?></button>Panitia PPDB<span>/</span><span style="color:var(--ink)"><?= e($title ?? 'Ringkasan') ?></span><span class="grow"></span><span><?= tgl(date('Y-m-d')) ?></span></div>
        <div class="page <?= !empty($wide) ? 'wide' : '' ?>">
            <div class="page-title"><div><div class="icon-big"><?= icon($titleIcon ?? 'home', 26) ?></div><h1><?= e($title ?? 'Ringkasan') ?></h1><?php if (!empty($subtitle)): ?><p><?= $subtitle ?></p><?php endif; ?></div><div class="page-actions"><?= $actions ?? '' ?></div></div>
            <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
