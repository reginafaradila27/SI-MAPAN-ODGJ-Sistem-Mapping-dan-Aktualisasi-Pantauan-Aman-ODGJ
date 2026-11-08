
<?php
require_once 'config/db.php';
auth();

// Total pasien ODGJ
[$w, $args] = patient_scope();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM patients p WHERE $w"
);
$stmt->execute($args);
$total = $stmt->fetchColumn();

// Total pasien dalam pemantauan
$stmt = $pdo->prepare(
    "SELECT COUNT(DISTINCT m.patient_id)
     FROM monitoring m
     JOIN patients p ON p.id = m.patient_id
     WHERE m.status = 'Pemantauan' AND $w"
);
$stmt->execute($args);
$monitor = $stmt->fetchColumn();

// Total pasien dalam penanganan
$stmt = $pdo->prepare(
    "SELECT COUNT(DISTINCT m.patient_id)
     FROM monitoring m
     JOIN patients p ON p.id = m.patient_id
     WHERE m.status = 'Penanganan' AND $w"
);
$stmt->execute($args);
$handling = $stmt->fetchColumn();

// Jumlah Puskesmas
$pusk = $pdo->query(
    "SELECT COUNT(*) FROM puskesmas"
)->fetchColumn();

// Daftar Puskesmas
$list = $pdo->query(
    "SELECT id, nama FROM puskesmas ORDER BY nama"
)->fetchAll();

include 'partials/header.php';
?>

<div class="heading">
    <h1>Dashboard SI-MAPAN ODGJ</h1>
    <p>
        Sistem Mapping dan Aktualisasi Pantauan Aman
        Orang dengan Gangguan Jiwa
    </p>
</div>

<!-- STATISTIK DASHBOARD -->
<div class="stats">

    <div class="stat">
        <div class="ico">♟</div>
        <div>
            Total Pasien ODGJ
            <strong><?= e($total) ?></strong>
        </div>
    </div>

    <div class="stat">
        <div class="ico">✓</div>
        <div>
            Dalam Pemantauan
            <strong><?= e($monitor) ?></strong>
        </div>
    </div>

    <div class="stat">
        <div class="ico">✚</div>
        <div>
            Dalam Penanganan
            <strong><?= e($handling) ?></strong>
        </div>
    </div>

    <div class="stat">
        <div class="ico">♧</div>
        <div>
            Puskesmas Terdaftar
            <strong><?= e($pusk) ?></strong>
        </div>
    </div>

</div>

<!-- KONTEN UTAMA DASHBOARD -->
<div class="grid">

    <!-- PANEL BIODATA PASIEN -->
    <section class="panel">

        <div class="panel-title">
            ♟ Biodata Pasien ODGJ

            <a class="btn small" href="patient_form.php">
                + Tambah Data Pasien
            </a>
        </div>

        <div class="panel-body">

            <p>
                Masukkan biodata melalui formulir terpisah
                agar data lebih aman dan mudah dikelola.
            </p>

            <a class="btn" href="patient_form.php">
                Buka Form Biodata Pasien
            </a>

            <a class="btn secondary" href="patients.php">
                Lihat Semua Pasien
            </a>

        </div>
    </section>

    <!-- PANEL DAFTAR PUSKESMAS -->
    <section class="panel">

        <div class="panel-title">
            ♙ Daftar Puskesmas di Kota Banjarmasin
        </div>

        <div class="panel-body">

            <input
                class="search"
                placeholder="Cari nama puskesmas..."
                data-filter-list
            >

            <ol class="puskesmas-list">

                <?php foreach ($list as $i => $k): ?>

                    <li data-filter-item>
                        <b><?= $i + 1 ?></b>
                        <?= e($k['nama']) ?>
                    </li>

                <?php endforeach; ?>

            </ol>

        </div>
    </section>

</div>

<?php include 'partials/footer.php'; ?>
