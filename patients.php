
<?php
require_once 'config/db.php';

auth();

[$w, $args] = patient_scope();

// PENCARIAN PASIEN
$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $w .= ' AND (
        p.nama LIKE ?
        OR p.kode LIKE ?
        OR p.kecamatan LIKE ?
    )';

    array_push(
        $args,
        "%$q%",
        "%$q%",
        "%$q%"
    );
}

// AMBIL DATA PASIEN
$sql = "
    SELECT
        p.*,
        k.nama AS puskesmas
    FROM patients p
    JOIN puskesmas k
        ON k.id = p.id_puskesmas
    WHERE $w
    ORDER BY p.id DESC
    LIMIT 500
";

$stmt = $pdo->prepare($sql);
$stmt->execute($args);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'partials/header.php';
?>

<div class="heading">
    <h1>Data Pasien ODGJ</h1>
    <p>
        Data sensitif: hanya untuk pengguna
        yang berwenang
    </p>
</div>

<section class="panel">

    <div class="panel-title">
        Daftar Pasien

        <a href="patient_form.php" class="btn small">
            + Tambah Pasien
        </a>
    </div>

    <div class="panel-body">

        <!-- FORM PENCARIAN -->
        <form class="filters" method="get">

            <input
                class="search"
                type="text"
                name="q"
                value="<?= e($q) ?>"
                placeholder="Cari nama, kode, kecamatan"
            >

            <button type="submit" class="btn">
                Cari
            </button>

        </form>

        <!-- TABEL PASIEN -->
        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>TTL</th>
                        <th>JK</th>
                        <th>Usia</th>
                        <th>Alamat</th>
                        <th>Wali</th>
                        <th>Puskesmas</th>
                        <th>Google Maps</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($rows as $r): ?>

                        <?php
                        // VALIDASI LINK GOOGLE MAPS
                        $mapsUrl = trim(
                            (string)($r['maps_url'] ?? '')
                        );

                        $parsed = parse_url($mapsUrl);

                        $mapsHost = strtolower(
                            $parsed['host'] ?? ''
                        );

                        $mapsScheme = strtolower(
                            $parsed['scheme'] ?? ''
                        );

                        $allowedHosts = [
                            'google.com',
                            'www.google.com',
                            'maps.google.com',
                            'maps.app.goo.gl'
                        ];

                        $validMaps =
                            filter_var(
                                $mapsUrl,
                                FILTER_VALIDATE_URL
                            ) !== false
                            && $mapsScheme === 'https'
                            && in_array(
                                $mapsHost,
                                $allowedHosts,
                                true
                            );

                        // HITUNG USIA
                        $usia = '-';

                        if (!empty($r['tanggal_lahir'])) {
                            try {
                                $lahir = new DateTime(
                                    $r['tanggal_lahir']
                                );

                                $usia = $lahir->diff(
                                    new DateTime()
                                )->y;
                            } catch (Exception $ex) {
                                $usia = '-';
                            }
                        }
                        ?>

                        <tr>

                            <td><?= e($r['kode']) ?></td>

                            <td><?= e($r['nama']) ?></td>

                            <td>
                                <?= e($r['tempat_lahir']) ?>,
                                <?= e($r['tanggal_lahir']) ?>
                            </td>

                            <td>
                                <?= e($r['jenis_kelamin']) ?>
                            </td>

                            <td><?= e($usia) ?></td>

                            <td><?= e($r['alamat']) ?></td>

                            <td><?= e($r['nama_wali']) ?></td>

                            <td><?= e($r['puskesmas']) ?></td>

                            <!-- TOMBOL GOOGLE MAPS -->
                            <td>

                                <?php if ($validMaps): ?>

                                    <a
                                        href="<?= e($mapsUrl) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn small"
                                        title="Buka lokasi Google Maps"
                                    >
                                        📍 Buka Maps
                                    </a>

                                <?php else: ?>

                                    <span class="muted">
                                        Belum tersedia
                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- AKSI -->
                            <td>

                                <div class="actions">

                                    <a
                                        class="btn small"
                                        href="patient_form.php?id=<?= (int)$r['id'] ?>"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        class="inline"
                                        method="post"
                                        action="patient_delete.php"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= e(csrf()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$r['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn small danger"
                                            data-confirm="Hapus data pasien beserta monitoring?"
                                        >
                                            Hapus
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$rows): ?>

                        <tr>
                            <td colspan="10" class="muted">
                                Tidak ada data pasien.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</section>

<?php include 'partials/footer.php'; ?>
