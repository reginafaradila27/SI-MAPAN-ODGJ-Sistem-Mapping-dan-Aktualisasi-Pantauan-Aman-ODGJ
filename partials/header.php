
<?php
require_once __DIR__ . '/../config/db.php';
auth();

$page = basename($_SERVER['SCRIPT_NAME']);
$u = current_user();
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SI-MAPAN ODGJ</title>

    <link rel="stylesheet" href="assets/style.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <style>
        /* TOMBOL KEMBALI DI NAVBAR */
        .btn-kembali {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 21px;
            padding: 8px 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-kembali:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .btn-kembali:focus-visible {
            outline: 2px solid #ffffff;
            outline-offset: 2px;
        }

        .btn-kembali i {
            font-size: 21px;
        }
    </style>
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="brand">
            <div class="brain">✚</div>

            <h2>SI-MAPAN ODGJ</h2>

            <small>
                Sistem Mapping dan Aktualisasi<br>
                Pantauan Aman ODGJ
            </small>
        </div>

        <!-- MENU NAVIGASI -->
        <nav>

            <?php
            $menus = [
                ['index.php', 'fa-house', 'Dashboard'],
                ['patients.php', 'fa-users', 'Data Pasien ODGJ'],
                ['map.php', 'fa-location-dot', 'Peta Sebaran'],
                ['monitoring.php', 'fa-clipboard-check', 'Monitoring & Tindak Lanjut'],
                ['puskesmas.php', 'fa-hospital', 'Data Puskesmas'],
                ['reports.php', 'fa-file-lines', 'Laporan']
            ];

            foreach ($menus as $n):
            ?>

                <a
                    class="<?= $page === $n[0] ? 'active' : '' ?>"
                    href="<?= e($n[0]) ?>"
                >
                    <i class="fa-solid <?= e($n[1]) ?>"></i>
                    <?= e($n[2]) ?>
                </a>

            <?php endforeach; ?>

            <?php if ($u['role'] === 'admin'): ?>

                <a
                    class="<?= $page === 'users.php' ? 'active' : '' ?>"
                    href="users.php"
                >
                    <i class="fa-solid fa-user-gear"></i>
                    Pengguna
                </a>

            <?php endif; ?>

        </nav>

        <!-- FOOTER SIDEBAR -->
        <div class="sidebar-foot">
            Dinas Kesehatan Kota Banjarmasin<br>
            <small>Data bersifat rahasia</small>
        </div>

    </aside>

    <!-- MAIN -->
    <div class="main">

        <!-- TOPBAR -->
        <header class="topbar">

            <!-- TOMBOL PANAH KEMBALI -->
            <button
                type="button"
                class="btn-kembali"
                id="btnKembali"
                title="Kembali ke halaman sebelumnya"
                aria-label="Kembali ke halaman sebelumnya"
            >
                <i class="fa-solid fa-arrow-left"></i>
            </button>

            <!-- INFORMASI PENGGUNA -->
            <div>

                <i class="fa-solid fa-bell"></i>

                &nbsp;

                <i class="fa-solid fa-circle-user"></i>

                <?= e($u['nama']) ?>

                <small>
                    (<?= e($u['role']) ?>)
                </small>

                &nbsp;

                <a href="logout.php" class="logout">
                    Keluar
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>

            </div>

        </header>

        <!-- CONTENT -->
        <main class="content">

<script>
document.getElementById('btnKembali').addEventListener('click', function () {
    const fallback = 'index.php';

    // Kembali hanya jika halaman sebelumnya masih dari aplikasi yang sama
    let kembali = false;

    if (document.referrer) {
        try {
            const sebelumnya = new URL(document.referrer);

            if (
                sebelumnya.origin === window.location.origin &&
                sebelumnya.pathname.startsWith(
                    window.location.pathname.substring(
                        0,
                        window.location.pathname.lastIndexOf('/') + 1
                    )
                )
            ) {
                kembali = true;
            }
        } catch (error) {
            kembali = false;
        }
    }

    if (kembali && window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = fallback;
    }
});
</script>
