
<?php
require_once 'config/db.php';

auth();

[$w, $args] = patient_scope();

// Menghitung jumlah pasien berdasarkan kecamatan
$sql = "
    SELECT
        COALESCE(
            NULLIF(TRIM(p.kecamatan), ''),
            'Tidak Diketahui'
        ) AS kecamatan,
        COUNT(*) AS jumlah
    FROM patients p
    WHERE $w
    GROUP BY kecamatan
    ORDER BY kecamatan
";

$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Titik pusat kecamatan (perkiraan untuk visualisasi agregat)
// Bukan koordinat lokasi rumah pasien
$lokasiKecamatan = [
    'Banjarmasin Utara' => [-3.2890, 114.5930],
    'Banjarmasin Barat' => [-3.3120, 114.5620],
    'Banjarmasin Tengah' => [-3.3190, 114.5910],
    'Banjarmasin Timur' => [-3.3280, 114.6150],
    'Banjarmasin Selatan' => [-3.3500, 114.5910]
];

$pins = [];
$totalPasien = 0;

foreach ($rows as $row) {

    $kecamatan = trim($row['kecamatan']);
    $jumlah = (int)$row['jumlah'];

    $totalPasien += $jumlah;

    if (isset($lokasiKecamatan[$kecamatan])) {

        $pins[] = [
            'kecamatan' => $kecamatan,
            'jumlah' => $jumlah,
            'lat' => $lokasiKecamatan[$kecamatan][0],
            'lng' => $lokasiKecamatan[$kecamatan][1]
        ];
    }
}

include 'partials/header.php';
?>

<div class="heading">
    <h1>Peta Sebaran ODGJ</h1>
    <p>
        Visualisasi jumlah pasien ODGJ berdasarkan
        kecamatan di Kota Banjarmasin
    </p>
</div>

<div class="note">
    Peta menampilkan data agregat berdasarkan kecamatan,
    bukan lokasi rumah atau identitas pasien.
    Gunakan data fiktif untuk presentasi publik.
</div>

<section class="panel">

    <div class="panel-title">
        Pemetaan Sebaran Pasien per Kecamatan
    </div>

    <div class="panel-body">

        <div id="map" style="height:480px;width:100%;"></div>

        <p class="muted" style="margin-top:15px;">
            Total pasien: <?= e($totalPasien) ?> |
            Kecamatan dengan data:
            <?= count($pins) ?>
        </p>

    </div>

</section>

<section class="panel" style="margin-top:20px;">

    <div class="panel-title">
        Rekap Jumlah Pasien per Kecamatan
    </div>

    <div class="panel-body table-wrap">

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kecamatan</th>
                    <th>Jumlah Pasien</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($rows as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($row['kecamatan']) ?></td>
                        <td><?= e($row['jumlah']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="3" class="muted">
                            Belum ada data pasien.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>

    </div>

</section>

<script>
window.SIMAPAN_PINS = <?= json_encode(
    $pins,
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS |
    JSON_HEX_QUOT
) ?>;

document.addEventListener('DOMContentLoaded', function () {

    if (!window.L) {
        console.error('Leaflet belum dimuat.');
        return;
    }

    const map = L.map('map').setView(
        [-3.3194, 114.5908],
        12
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }
    ).addTo(map);

    const markers = [];

    window.SIMAPAN_PINS.forEach(function (data) {

        const marker = L.circleMarker(
            [data.lat, data.lng],
            {
                radius: Math.min(
                    10 + data.jumlah * 2,
                    30
                ),
                color: '#2563eb',
                fillColor: '#3b82f6',
                fillOpacity: 0.55,
                weight: 2
            }
        ).addTo(map);

        const popup = document.createElement('div');

        const title = document.createElement('strong');
        title.textContent = data.kecamatan;

        const count = document.createElement('p');
        count.textContent =
            'Jumlah Pasien: ' + data.jumlah;

        popup.appendChild(title);
        popup.appendChild(count);

        marker.bindPopup(popup);

        markers.push(marker);
    });

    if (markers.length > 0) {

        const group = L.featureGroup(markers);

        map.fitBounds(
            group.getBounds().pad(0.25),
            { maxZoom: 13 }
        );
    }

});
</script>

<?php include 'partials/footer.php'; ?>
