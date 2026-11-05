<?php $set = setting(); $flash = flash(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'PPDB Online') ?> · PPDB <?= e($set['nama_sekolah']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header style="border-bottom:1px solid var(--line)">
    <div class="pub-top">
        <span class="logo"><?= icon('school', 18) ?></span><strong>PPDB <?= e($set['nama_sekolah']) ?></strong><span class="muted">· <?= e($set['tahun_ajaran']) ?></span>
        <nav>
            <a href="<?= url('beranda') ?>">Informasi</a>
            <a href="<?= url('pengumuman') ?>">Pengumuman</a>
            <?php if (pendaftar()): ?><a href="<?= url('status') ?>">Status saya</a><a href="<?= url('keluar') ?>">Keluar</a>
            <?php else: ?><a href="<?= url('masuk') ?>">Masuk</a><a class="btn btn-primary" href="<?= url('daftar') ?>" style="color:#fff">Daftar</a><?php endif; ?>
        </nav>
    </div>
</header>
<main class="page">
    <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
