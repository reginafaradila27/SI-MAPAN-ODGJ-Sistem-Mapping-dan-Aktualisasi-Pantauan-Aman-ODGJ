
<?php
require_once 'config/db.php';
auth();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$error = '';
$u = current_user();

$row = [
    'kode' => '',
    'nama' => '',
    'tempat_lahir' => '',
    'tanggal_lahir' => '',
    'jenis_kelamin' => 'L',
    'alamat' => '',
    'kelurahan' => '',
    'kecamatan' => '',
    'maps_url' => '',
    'nama_wali' => '',
    'no_telepon_wali' => '',
    'id_puskesmas' => ''
];

// Mengambil data pasien saat edit
if ($id > 0) {
    if (!patient_access($pdo, $id)) {
        http_response_code(403);
        exit('Akses ditolak');
    }

    $stmt = $pdo->prepare(
        "SELECT * FROM patients WHERE id = ?"
    );
    $stmt->execute([$id]);

    $patient = $stmt->fetch();

    if (!$patient) {
        http_response_code(404);
        exit('Data pasien tidak ditemukan');
    }

    $row = array_merge($row, $patient);
}

// Proses simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $fields = [
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'kelurahan',
        'kecamatan',
        'maps_url',
        'nama_wali',
        'no_telepon_wali',
        'id_puskesmas'
    ];

    foreach ($fields as $field) {
        $row[$field] = trim(
            (string)($_POST[$field] ?? '')
        );
    }

    $date = DateTime::createFromFormat(
        '!Y-m-d',
        $row['tanggal_lahir']
    );

    $validDate = $date
        && $date->format('Y-m-d') === $row['tanggal_lahir']
        && $date <= new DateTime();

    if (
        $row['nama'] === '' ||
        !$validDate ||
        !in_array(
            $row['jenis_kelamin'],
            ['L', 'P'],
            true
        ) ||
        $row['alamat'] === '' ||
        $row['nama_wali'] === '' ||
        $row['no_telepon_wali'] === '' ||
        $row['id_puskesmas'] === ''
    ) {
        $error = 'Harap lengkapi semua kolom wajib.';
    } elseif (
        $row['maps_url'] !== '' &&
        !filter_var(
            $row['maps_url'],
            FILTER_VALIDATE_URL
        )
    ) {
        $error = 'URL Google Maps tidak valid.';
    } else {

        // Validasi Puskesmas
        $stmt = $pdo->prepare(
            "SELECT 1 FROM puskesmas WHERE id = ?"
        );

        $stmt->execute([
            $row['id_puskesmas']
        ]);

        if (!$stmt->fetchColumn()) {
            $error = 'Puskesmas tidak valid.';
        } else {

            if ($id > 0) {

                // Update data pasien
                $sql = "
                    UPDATE patients SET
                        nama = ?,
                        tempat_lahir = ?,
                        tanggal_lahir = ?,
                        jenis_kelamin = ?,
                        alamat = ?,
                        kelurahan = ?,
                        kecamatan = ?,
                        maps_url = ?,
                        nama_wali = ?,
                        no_telepon_wali = ?,
                        id_puskesmas = ?
                    WHERE id = ?
                ";

                $stmt = $pdo->prepare($sql);

                $values = array_map(
                    fn($field) => $row[$field],
                    $fields
                );

                $stmt->execute([
                    ...$values,
                    $id
                ]);

            } else {

                // Tambah data pasien
                $kode = 'ODGJ-' .
                    strtoupper(
                        bin2hex(random_bytes(5))
                    );

                $sql = "
                    INSERT INTO patients (
                        kode,
                        nama,
                        tempat_lahir,
                        tanggal_lahir,
                        jenis_kelamin,
                        alamat,
                        kelurahan,
                        kecamatan,
                        maps_url,
                        nama_wali,
                        no_telepon_wali,
                        id_puskesmas,
                        created_by
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?
                    )
                ";

                $stmt = $pdo->prepare($sql);

                $values = array_map(
                    fn($field) => $row[$field],
                    $fields
                );

                $stmt->execute([
                    $kode,
                    ...$values,
                    $u['id']
                ]);
            }

            redirect('patients.php');
        }
    }
}

// Mengambil daftar Puskesmas
$stmt = $pdo->query(
    "SELECT id, nama FROM puskesmas ORDER BY nama"
);

$pusk = $stmt->fetchAll();

include 'partials/header.php';
?>

<div class="heading">
    <h1>
        <?= $id ? 'Edit' : 'Tambah' ?>
        Biodata Pasien ODGJ
    </h1>
    <p>
        Informasi pribadi pasien harus dijaga
        kerahasiaannya.
    </p>
</div>

<div class="panel">
    <div class="panel-title">
        Form Biodata Pasien
    </div>

    <div class="panel-body">

        <?php if ($error): ?>
            <div class="alert error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf()) ?>"
            >

            <input
                type="hidden"
                name="id"
                value="<?= $id ?>"
            >

            <div class="form-grid">

                <div class="field">
                    <label>Nama Pasien *</label>
                    <input
                        name="nama"
                        value="<?= e($row['nama']) ?>"
                        required
                        maxlength="150"
                    >
                </div>

                <div class="field">
                    <label>Nama Wali *</label>
                    <input
                        name="nama_wali"
                        value="<?= e($row['nama_wali']) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label>Tempat Lahir</label>
                    <input
                        name="tempat_lahir"
                        value="<?= e($row['tempat_lahir']) ?>"
                    >
                </div>

                <div class="field">
                    <label>Tanggal Lahir *</label>
                    <input
                        type="date"
                        id="tanggal_lahir"
                        name="tanggal_lahir"
                        value="<?= e($row['tanggal_lahir']) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label>Nomor Wali *</label>
                    <input
                        name="no_telepon_wali"
                        value="<?= e($row['no_telepon_wali']) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label>Jenis Kelamin *</label>
                    <select name="jenis_kelamin">
                        <option value="L"
                            <?= $row['jenis_kelamin'] === 'L'
                                ? 'selected' : '' ?>>
                            Laki-laki
                        </option>

                        <option value="P"
                            <?= $row['jenis_kelamin'] === 'P'
                                ? 'selected' : '' ?>>
                            Perempuan
                        </option>
                    </select>
                </div>

                <div class="field">
                    <label>Usia (otomatis)</label>
                    <input id="age" readonly>
                </div>

                <div class="field">
                    <label>Puskesmas / Instansi *</label>

                    <select name="id_puskesmas" required>
                        <option value="">
                            Pilih Puskesmas
                        </option>

                        <?php foreach ($pusk as $k): ?>
                            <option
                                value="<?= (int)$k['id'] ?>"
                                <?= (int)$row['id_puskesmas']
                                    === (int)$k['id']
                                    ? 'selected' : '' ?>
                            >
                                <?= e($k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field full">
                    <label>Alamat Tempat Tinggal *</label>
                    <textarea
                        name="alamat"
                        required
                    ><?= e($row['alamat']) ?></textarea>
                </div>

                <div class="field">
                    <label>Kelurahan</label>
                    <input
                        name="kelurahan"
                        value="<?= e($row['kelurahan']) ?>"
                    >
                </div>

                <div class="field">
                    <label>Kecamatan</label>
                    <input
                        name="kecamatan"
                        value="<?= e($row['kecamatan']) ?>"
                    >
                </div>

                <div class="field full">
                    <label>
                        URL Lokasi Google Maps (opsional)
                    </label>
                    <input
                        type="url"
                        name="maps_url"
                        value="<?= e($row['maps_url']) ?>"
                        placeholder="https://maps.google.com/..."
                    >
                </div>

            </div>

            <div class="actions" style="margin-top:20px">
                <a
                    class="btn secondary"
                    href="patients.php"
                >
                    Batal
                </a>

                <button type="submit" class="btn">
                    Simpan Data
                </button>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const birthInput = document.getElementById('tanggal_lahir');
    const ageInput = document.getElementById('age');

    function calculateAge() {
        if (!birthInput.value) {
            ageInput.value = '';
            return;
        }

        const birth = new Date(birthInput.value + 'T00:00:00');
        const today = new Date();

        let age = today.getFullYear() - birth.getFullYear();

        const monthDiff = today.getMonth() - birth.getMonth();

        if (
            monthDiff < 0 ||
            (monthDiff === 0 &&
                today.getDate() < birth.getDate())
        ) {
            age--;
        }

        ageInput.value = age >= 0 ? age + ' Tahun' : '';
    }

    birthInput.addEventListener('change', calculateAge);
    calculateAge();
});
</script>

<?php include 'partials/footer.php'; ?>
