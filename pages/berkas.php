<?php
// Berkas pendaftar hanya bisa dibuka oleh panitia atau pemilik berkas
$b = q('SELECT * FROM berkas WHERE id = ?', [(int) ($_GET['id'] ?? 0)])->fetch();
$p = pendaftar();
if (!$b || (!user() && (!$p || $p['id'] != $b['pendaftar_id']))) { http_response_code(404); exit('Berkas tidak ditemukan.'); }
$path = __DIR__ . '/../berkas/' . basename($b['file']);
if (!is_file($path)) { http_response_code(404); exit('File tidak ada.'); }
header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($path));
header('Content-Disposition: inline; filename="' . basename($b['file']) . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
