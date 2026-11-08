# SI-MAPAN ODGJ

**Sistem Mapping dan Aktualisasi Pantauan Aman ODGJ**

Aplikasi web untuk membantu pengelolaan data, pemetaan sebaran, pemantauan kondisi, tindak lanjut, serta pelaporan terkait Orang Dengan Gangguan Jiwa (ODGJ). Proyek ini dikembangkan sebagai sistem pendukung pencatatan dan evaluasi bagi lingkungan **Dinas Kesehatan Kota Banjarmasin**.

> **Pemberitahuan:** Repositori ini ditujukan untuk pengembangan dan demonstrasi. Gunakan **data fiktif** saat menguji atau memublikasikan aplikasi. Data kesehatan merupakan informasi sensitif yang wajib dilindungi. Nama instansi dalam deskripsi proyek tidak berarti aplikasi telah menjadi layanan resmi instansi.

## Fitur Utama

- **Login dan manajemen sesi** — autentikasi pengguna dengan password yang diverifikasi menggunakan `password_verify()`.
- **Dashboard** — ringkasan informasi dan statistik aplikasi.
- **Data Pasien ODGJ** — pengelolaan identitas dan informasi pendukung pasien.
- **Peta Sebaran** — visualisasi agregat jumlah pasien berdasarkan kecamatan menggunakan Leaflet.
- **Monitoring dan Tindak Lanjut** — pencatatan tanggal monitoring, status penanganan, perkembangan pasien, dan tindak lanjut.
- **Progres Pasien** — pilihan *Rutin Mengonsumsi Obat*, *Tidak Rutin*, dan *Putus Obat*.
- **Data Puskesmas** — pengelolaan informasi Puskesmas.
- **Laporan** — penyajian data pasien, Puskesmas, wilayah, monitoring, dan penanganan, termasuk ekspor CSV.
- **Manajemen Pengguna** — pengelolaan pengguna berdasarkan peran yang disediakan aplikasi (`admin`, `dinkes`, dan `puskesmas`).

## Teknologi

| Komponen | Teknologi |
| --- | --- |
| Backend | PHP Native |
| Database | MySQL/MariaDB, melalui PDO |
| Frontend | HTML, CSS, JavaScript |
| Peta | Leaflet.js |
| Ikon | Font Awesome |
| Server lokal | Apache dan MySQL/MariaDB melalui XAMPP |

## Struktur Proyek

```text
simapan_odgj/
├── assets/
│   ├── style.css
│   ├── app.js
│   └── dinkes.webp
├── config/
│   └── db.php
├── partials/
│   ├── header.php
│   └── footer.php
├── index.php
├── login.php
├── logout.php
├── patients.php
├── map.php
├── monitoring.php
├── puskesmas.php
├── reports.php
└── users.php
```

> Struktur di atas menunjukkan berkas utama aplikasi. Struktur sebenarnya dapat mencakup berkas tambahan.

## Instalasi Lokal dengan XAMPP

1. Instal XAMPP yang menyediakan Apache, PHP, dan MySQL/MariaDB.
2. Salin folder proyek ke:

   ```text
   C:\xampp\htdocs\simapan_odgj
   ```

3. Jalankan **Apache** dan **MySQL** melalui XAMPP Control Panel.
4. Buka `http://localhost/phpmyadmin` lalu buat database bernama `simapan_odgj`.
5. **Impor skema SQL aplikasi** jika tersedia. README ini tidak menyertakan dump database; pastikan seluruh tabel yang dibutuhkan aplikasi sudah dibuat.
6. Konfigurasikan koneksi database pada `config/db.php` sesuai lingkungan lokal. **Jangan mengganti seluruh isi berkas** jika berkas tersebut juga mendefinisikan fungsi autentikasi dan utilitas aplikasi.
7. Buka:

   ```text
   http://localhost/simapan_odgj/login.php
   ```

8. Masuk menggunakan akun pengguna yang telah dibuat pada database lokal. **Repositori tidak menyertakan kredensial default.**

## Catatan Konfigurasi

Aplikasi membutuhkan konfigurasi koneksi PDO ke database MySQL/MariaDB. Sesuaikan nama host, database, username, dan password dengan lingkungan masing-masing.

**Jangan pernah mengunggah ke repositori publik:**

- Password atau kredensial database.
- File `.env` atau konfigurasi produksi yang memuat rahasia.
- Backup SQL yang mengandung data pribadi atau data pasien.
- Berkas unggahan dan dokumen pasien asli.

Contoh `.gitignore` yang dapat disesuaikan:

```gitignore
.env
/config/db.php
*.sql
/backups/
/uploads/
```

> Jika `config/db.php` dikecualikan dari Git, pengelola instalasi perlu menyediakan berkas tersebut secara terpisah. Jangan sampai fungsi penting di dalamnya hilang saat melakukan konfigurasi.

## Hosting dan Deployment

**GitHub Pages tidak menjalankan PHP maupun MySQL.** Karena itu, mengunggah proyek ini ke GitHub Pages tidak akan membuat fitur login, sesi, dan pengelolaan database berfungsi; berkas `.php` bahkan dapat terunduh alih-alih dieksekusi.

Untuk menjalankan aplikasi secara online, gunakan server/hosting yang mendukung:

- PHP dan ekstensi PDO MySQL.
- Database MySQL/MariaDB.
- HTTPS.
- Penyimpanan dan konfigurasi kredensial yang aman.
- Pembatasan akses, pembaruan keamanan, dan pencadangan.

GitHub dapat digunakan sebagai penyimpanan source code, sedangkan eksekusi aplikasi berlangsung pada server PHP yang terpisah.

## Peran Pengguna

Aplikasi mengenal beberapa peran:

| Peran | Keterangan |
| --- | --- |
| `admin` | Administrasi sistem, termasuk pengelolaan pengguna. |
| `dinkes` | Pengguna dalam konteks Dinas Kesehatan. |
| `puskesmas` | Pengguna dalam konteks Puskesmas. |

Hak akses terperinci mengikuti implementasi autentikasi dan pembatasan akses pada kode aplikasi.

## Keamanan dan Kerahasiaan Data

- Gunakan data dummy/fiktif untuk demo, screenshot, repository, dan layanan hosting gratis.
- Hindari menyimpan identitas atau riwayat kesehatan pasien asli dalam repository GitHub.
- Terapkan autentikasi, pembatasan hak akses, perlindungan CSRF, dan validasi input pada seluruh aksi yang relevan.
- Gunakan HTTPS untuk akses di luar komputer lokal.
- Jangan gunakan server publik untuk data pasien asli tanpa persetujuan dan pengamanan yang memadai dari instansi.

## Status Proyek

Proyek pengembangan aplikasi web PHP Native dan MySQL. Fitur, tampilan, dan struktur database dapat berubah selama proses pengembangan.

---

**SI-MAPAN ODGJ** — Sistem Mapping dan Aktualisasi Pantauan Aman ODGJ  
*Dinas Kesehatan Kota Banjarmasin · Akses terbatas · Lindungi kerahasiaan data pasien*

