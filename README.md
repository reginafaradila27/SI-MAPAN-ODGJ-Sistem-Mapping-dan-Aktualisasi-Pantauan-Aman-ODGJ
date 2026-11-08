# SI-MAPAN ODGJ — PHP Native + MySQL

Aplikasi contoh PKL berdasarkan dua gambar referensi yang diberikan. **Bukan sistem produksi siap pakai untuk data kesehatan nyata.**

## Cara menjalankan dengan XAMPP

1. Salin folder `simapan_odgj` ke `C:\xampp\htdocs\simapan_odgj`.
2. Jalankan Apache dan MySQL melalui XAMPP.
3. Buka `http://localhost/phpmyadmin`, pilih **Import**, impor `sql/simapan.sql`.
4. Sesuaikan kredensial database pada `config/db.php` jika berbeda dari default XAMPP (`root`, password kosong).
5. Buka `http://localhost/simapan_odgj/setup.php`, buat akun admin pertama dengan password kuat minimal 12 karakter.
6. **Hapus file `setup.php` setelah berhasil membuat admin**.
7. Buka `http://localhost/simapan_odgj/login.php` dan masuk dengan akun yang dibuat.

## Menu
- Login dan logout
- Dashboard dengan statistik dari database
- Tambah, cari, ubah, hapus data pasien
- Usia dihitung otomatis dari tanggal lahir
- Data wali, Puskesmas dan lokasi Google Maps/koordinat
- Peta Leaflet + OpenStreetMap (memerlukan internet untuk tile)
- Monitoring dan tindak lanjut
- 5 laporan: data pasien, per Puskesmas, per wilayah, monitoring, rekap penanganan
- Manajemen pengguna (admin)
- Role admin, dinkes, puskesmas; pengguna Puskesmas dibatasi ke pasien Puskesmas sendiri

## Penting tentang gambar referensi
Angka 126 pasien, 98 pemantauan, 20 penanganan, dan 28 Puskesmas pada gambar **hanya contoh desain**. Dashboard aplikasi menampilkan angka nyata dari database. Daftar Puskesmas SQL hanya memuat 11 nama awal yang terlihat pada gambar, **bukan daftar resmi lengkap**.

## Keamanan dan privasi
Gunakan **data fiktif** saat demo atau laporan. Jangan masukkan data pasien nyata tanpa persetujuan instansi dan penilaian keamanan serta kepatuhan yang sesuai. Aplikasi prototipe ini memakai PDO prepared statements, password hashing, CSRF, escaping output, dan pembatasan akses berdasarkan role. Untuk produksi, masih perlu HTTPS, audit log, mekanisme reset password, rate limiting login, kontrol retensi dan penghapusan data, enkripsi backup, hardening server, evaluasi akses detail, dan penilaian dampak perlindungan data. Lokasi pasien bersifat sangat sensitif.

## Struktur
- `config/db.php`: koneksi dan helper keamanan
- `sql/simapan.sql`: database dan tabel
- `assets/`: CSS, JS, gambar referensi
- `partials/`: header dan footer
- `setup.php`: pembuatan admin pertama
- `login.php`, `index.php`, `patients.php`, `patient_form.php`, `patient_delete.php`, `puskesmas.php`, `monitoring.php`, `map.php`, `reports.php`, `users.php`, `logout.php`

## Catatan teknis
PHP 8.0+ dan MySQL/MariaDB modern disarankan. Koneksi internet dibutuhkan untuk ikon Font Awesome, Leaflet dan peta OpenStreetMap. Tautan Google Maps pasien disimpan dalam database; peta internal menampilkan koordinat latitude/longitude yang diinput.
