# PPDB Online - Penerimaan Peserta Didik Baru (PHP Native + MySQL)

Sistem PPDB Online berbasis PHP Native dan MySQL untuk SD, SMP, SMA, SMK, madrasah, dan pesantren. Calon siswa mendaftar sendiri dari rumah: membuat akun dengan NISN, mengisi formulir bertahap (biodata, data orang tua, nilai dan prestasi), mengunggah berkas (KK, akta, rapor, pas foto, dokumen jalur), lalu memantau status dan mencetak kartu pendaftaran.

Panitia memverifikasi berkas satu per satu, bisa meminta perbaikan dengan catatan, lalu menjalankan seleksi otomatis per jalur sesuai kuota: Zonasi berdasarkan jarak, Prestasi berdasarkan nilai rapor ditambah poin prestasi, serta Afirmasi dan Perpindahan Tugas Orang Tua. Hasil seleksi baru tampil ke calon siswa sesuai jadwal pengumuman. Tampilannya bergaya workspace minimalis yang bersih dan nyaman, cocok dipakai langsung atau sebagai bahan tugas kuliah dan skripsi.

## Fitur

- Pendaftaran online mandiri: buat akun dengan NISN, dapat nomor pendaftaran otomatis
- Formulir 4 langkah dengan penanda kelengkapan: biodata, orang tua, nilai & prestasi, berkas
- Unggah berkas KK, akta, rapor, pas foto, dan dokumen jalur (validasi PDF/JPG/PNG maks 2 MB)
- Berkas tersimpan di folder terlindungi, hanya bisa dibuka pemilik dan panitia
- 4 jalur: Zonasi, Prestasi, Afirmasi, Perpindahan Tugas Orang Tua, masing-masing dengan kuota
- Halaman status calon siswa dengan progres dan pesan sesuai tahap
- Cetak kartu pendaftaran lengkap dengan pas foto
- Antrean verifikasi berkas untuk panitia, otomatis lanjut ke pendaftar berikutnya
- Minta perbaikan berkas dengan catatan yang langsung terlihat calon siswa
- Penyesuaian poin prestasi oleh verifikator
- Seleksi otomatis per jalur dengan rumus skor dan tie-breaker yang transparan
- Penetapan diterima, cadangan (persen bisa diatur), dan tidak diterima sesuai kuota
- Hasil hanya tampil ke calon siswa setelah jadwal pengumuman
- Halaman pengumuman publik: cek hasil dengan nomor pendaftaran
- Pencatatan daftar ulang siswa yang diterima
- Dashboard: pendaftar vs kuota per jalur, grafik formulir masuk, asal sekolah terbanyak
- Tahapan PPDB otomatis dari jadwal (dibuka, ditutup, pengumuman)
- 2 peran: Admin (ketua panitia) dan Verifikator
- Keamanan: PDO prepared statement, password_hash, proteksi CSRF, batas percobaan login
- Data contoh 268 pendaftar dan tampilan workspace minimalis yang responsif

## Instalasi

**Kebutuhan:** PHP 8.0+ (ekstensi `fileinfo` aktif) dan MySQL/MariaDB (XAMPP atau Laragon).

1. Ekstrak ZIP, lalu pindahkan folder `ppdb-online` ke:
   - XAMPP: `C:\xampp\htdocs\ppdb-online`
   - Laragon: `C:\laragon\www\ppdb-online`

2. Jalankan **Apache** dan **MySQL**.

3. Buka `http://localhost/phpmyadmin` → **Import** → pilih `database/ppdb_online.sql` → **Import**.

   Atau lewat terminal:
   ```bash
   mysql -u root -p < database/ppdb_online.sql
   ```
   Jadwal contoh: pendaftaran sedang dibuka (ditutup 3 hari setelah import, pengumuman 7 hari setelah import).

4. Sesuaikan `config/database.php` jika user/password MySQL kamu berbeda:
   ```php
   return [
       'host'     => 'localhost',
       'port'     => 3306,
       'database' => 'ppdb_online',
       'username' => 'root',
       'password' => '',
   ];
   ```

5. **Halaman calon siswa:** `http://localhost/ppdb-online` → klik **Daftar**.

6. **Panel panitia:** `http://localhost/ppdb-online/index.php?page=login`
   - Ketua panitia: `admin` / `admin123`
   - Verifikator: `verifikator` / `admin123`

7. Atur jadwal, kuota jalur, dan nama sekolah di menu **Jadwal & kuota**. Jalankan seleksi di menu **Seleksi & pengumuman**.

**Catatan:** folder `berkas/` dilindungi `.htaccess` (Apache). Untuk Nginx tambahkan `deny all` pada folder tersebut.

---
Source code ini dibagikan gratis oleh **Papicode** — https://papicode.id
