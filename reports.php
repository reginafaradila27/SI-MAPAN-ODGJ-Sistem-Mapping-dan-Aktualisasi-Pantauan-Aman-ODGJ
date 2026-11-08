
<?php
require_once 'config/db.php';
auth();

[$w, $args] = patient_scope();

$type = $_GET['type'] ?? 'pasien';

$types = [
    'pasien' => 'Laporan Data Pasien',
    'puskesmas' => 'Laporan Pasien per Puskesmas',
    'wilayah' => 'Laporan Persebaran Wilayah',
    'monitoring' => 'Laporan Monitoring',
    'penanganan' => 'Laporan Rekap Penanganan'
];

if (!isset($types[$type])) {
    $type = 'pasien';
}

$headers = [];
$rows = [];

// LAPORAN DATA PASIEN
if ($type === 'pasien') {

    $headers = [
        'Kode',
        'Nama',
        'Jenis Kelamin',
        'Usia',
        'Puskesmas'
    ];

    $sql = "
        SELECT
            p.kode,
            p.nama,
            p.jenis_kelamin,
            TIMESTAMPDIFF(
                YEAR,
                p.tanggal_lahir,
                CURDATE()
            ) AS usia,
            k.nama AS puskesmas
        FROM patients p
        JOIN puskesmas k
            ON k.id = p.id_puskesmas
        WHERE $w
        ORDER BY p.nama
    ";

}
// LAPORAN PER PUSKESMAS
elseif ($type === 'puskesmas') {

    $headers = [
        'Puskesmas',
        'Jumlah Pasien'
    ];

    $sql = "
        SELECT
            k.nama AS puskesmas,
            COUNT(*) AS jumlah
        FROM patients p
        JOIN puskesmas k
            ON k.id = p.id_puskesmas
        WHERE $w
        GROUP BY k.id, k.nama
        ORDER BY k.nama
    ";

}
// LAPORAN WILAYAH
elseif ($type === 'wilayah') {

    $headers = [
        'Kecamatan',
        'Jumlah Pasien'
    ];

    $sql = "
        SELECT
            COALESCE(
                NULLIF(p.kecamatan, ''),
                'Tidak dicatat'
            ) AS kecamatan,
            COUNT(*) AS jumlah
        FROM patients p
        WHERE $w
        GROUP BY kecamatan
        ORDER BY kecamatan
    ";

}
// LAPORAN MONITORING
elseif ($type === 'monitoring') {

    $headers = [
        'Tanggal',
        'Kode Pasien',
        'Status',
        'Tindak Lanjut'
    ];

    $sql = "
        SELECT
            m.tanggal,
            p.kode,
            m.status,
            m.tindak_lanjut
        FROM monitoring m
        JOIN patients p
            ON p.id = m.patient_id
        WHERE $w
        ORDER BY m.tanggal DESC
        LIMIT 500
    ";

}
// LAPORAN REKAP PENANGANAN
else {

    $headers = [
        'Status',
        'Jumlah Catatan'
    ];

    $sql = "
        SELECT
            m.status,
            COUNT(*) AS jumlah
        FROM monitoring m
        JOIN patients p
            ON p.id = m.patient_id
        WHERE $w
        GROUP BY m.status
    ";
}

// EKSEKUSI QUERY
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll(PDO::FETCH_NUM);

// DOWNLOAD EXCEL (CSV)
if (($_GET['download'] ?? '') === 'excel') {

    // Hanya admin yang boleh mengekspor laporan
    admin();

    $filename = 'SI_MAPAN_ODGJ_' .
        $type . '_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header(
        'Content-Disposition: attachment; filename="' .
        $filename . '"'
    );
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM agar karakter terbaca di Excel
    fwrite($output, "\xEF\xBB\xBF");

    // Header kolom
    fputcsv($output, $headers, ';', '"', '');

    // Isi data
    foreach ($rows as $row) {

        // Mencegah formula injection saat dibuka di Excel
        $safeRow = array_map(function ($value) {
            $value = (string)($value ?? '');

            if (preg_match('/^[\s]*[=+\-@]/u', $value)) {
                return "'" . $value;
            }

            return $value;
        }, $row);

        fputcsv($output, $safeRow, ';', '"', '');
    }

    fclose($output);
    exit;
}

include 'partials/header.php';
?>

<div class="heading">
    <h1>Laporan SI-MAPAN ODGJ</h1>
    <p>Hanya untuk keperluan internal instansi</p>
</div>

<div class="note">
    Laporan individu berisi data sensitif.
    Hindari membagikannya kepada pihak yang
    tidak berwenang. Untuk publikasi gunakan
    rekap agregat.
</div>

<section class="panel">

    <div class="panel-title">
        <?= e($types[$type]) ?>
    </div>

    <div class="panel-body">

        <form class="filters" method="get">

            <select class="search" name="type">
                <?php foreach ($types as $k => $v): ?>
                    <option
                        value="<?= e($k) ?>"
                        <?= $type === $k ? 'selected' : '' ?>
                    >
                        <?= e($v) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn">
                Tampilkan
            </button>

            <button
                type="button"
                class="btn secondary"
                onclick="window.print()"
            >
                Cetak / PDF
            </button>

            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a
                    class="btn"
                    href="reports.php?type=<?= urlencode($type) ?>&download=excel"
                >
                    Download Excel
                </a>
            <?php endif; ?>

        </form>

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <?php foreach ($headers as $h): ?>
                            <th><?= e($h) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?= e($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="<?= count($headers) ?>">
                                Belum ada data.
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</section>

<?php include 'partials/footer.php'; ?>
