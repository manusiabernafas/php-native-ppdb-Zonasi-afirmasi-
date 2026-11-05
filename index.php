<?php
require __DIR__ . '/includes/bootstrap.php';

$routes = [
    // Publik / calon siswa
    'beranda'    => 'pages/daftar/beranda.php',
    'daftar'     => 'pages/daftar/daftar.php',
    'masuk'      => 'pages/daftar/masuk.php',
    'formulir'   => 'pages/daftar/formulir.php',
    'status'     => 'pages/daftar/status.php',
    'kartu'      => 'pages/daftar/kartu.php',
    'pengumuman' => 'pages/daftar/pengumuman.php',
    'keluar'     => 'pages/daftar/keluar.php',
    'berkas'     => 'pages/berkas.php',
    // Panitia
    'login'      => 'pages/admin/login.php',
    'logout'     => 'pages/admin/logout.php',
    'dashboard'  => 'pages/admin/dashboard.php',
    'pendaftar'  => 'pages/admin/pendaftar.php',
    'verifikasi' => 'pages/admin/verifikasi.php',
    'seleksi'    => 'pages/admin/seleksi.php',
    'pengaturan' => 'pages/admin/pengaturan.php',
];
$publik = ['beranda', 'daftar', 'masuk', 'formulir', 'status', 'kartu', 'pengumuman', 'keluar', 'berkas', 'login'];

$page = $_GET['page'] ?? 'beranda';
if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'beranda';
}
if (!in_array($page, $publik, true)) require_login();
verify_csrf();

require __DIR__ . '/' . $routes[$page];
