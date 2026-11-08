
<?php
require 'config/db.php';
auth();

[$w, $args] = patient_scope();
$error = '';

$pilihanProgres = [
    'Rutin Mengonsumsi Obat',
    'Tidak Rutin',
    'Putus Obat'
];

$pilihanStatus = [
    'Pemantauan',
    'Penanganan',
    'Selesai'
];

$old = [
    'patient_id' => '',
    'tanggal' => date('Y-m-d'),
    'status' => 'Pemantauan',
    'progres_pasien' => '',
    'tindak_lanjut' => ''
];

// PROSES SIMPAN MONITORING
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $pid = (int) ($_POST['patient_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');
    $progres = trim($_POST['progres_pasien'] ?? '');
    $tindakLanjut = trim($_POST['tindak_lanjut'] ?? '');

    $old = [
        'patient_id' => $pid,
        'tanggal' => $tanggal,
        'status' => $status,
        'progres_pasien' => $progres,
        'tindak_lanjut' => $tindakLanjut
    ];

    // VALIDASI TANGGAL
    $tanggalValid = false;

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $dt = DateTime::createFromFormat('!Y-m-d', $tanggal);

        $tanggalValid = $dt !== false
            && $dt->format('Y-m-d') === $tanggal;
    }

    // VALIDASI DATA
    if (
        !patient_access($pdo, $pid) ||
        !in_array($status, $pilihanStatus, true) ||
        !in_array($progres, $pilihanProgres, true) ||
        !$tanggalValid
    ) {
        $error = 'Data monitoring tidak valid atau akses ditolak.';
    } else {

        $s = $pdo->prepare("
            INSERT INTO monitoring
            (
                patient_id,
                tanggal,
                status,
                progres_pasien,
                tindak_lanjut,
                created_by
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $s->execute([
            $pid,
            $tanggal,
            $status,
            $progres,
            $tindakLanjut,
            $_SESSION['uid']
        ]);

        redirect('monitoring.php');
        exit;
    }
}

// AMBIL DAFTAR PASIEN
$s = $pdo->prepare("
    SELECT
        p.id,
        p.nama
    FROM patients p
    WHERE $w
    ORDER BY p.nama
");

$s->execute($args);
$patients = $s->fetchAll();

// AMBIL RIWAYAT MONITORING
$s = $pdo->prepare("
    SELECT
        m.*,
        p.nama AS pasien,
        u.nama AS petugas
    FROM monitoring m
    JOIN patients p
        ON p.id = m.patient_id
    LEFT JOIN users u
        ON u.id = m.created_by
    WHERE $w
    ORDER BY m.tanggal DESC, m.id DESC
    LIMIT 200
");

$s->execute($args);
$rows = $s->fetchAll();

include 'partials/header.php';
?>

<!-- STYLE WARNA PROGRES PASIEN -->
<style>
    .badge-progres {
        display: inline-block;
        padding: 7px 13px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        text-align: center;
        white-space: nowrap;
    }

    /* HIJAU - RUTIN */
    .progres-rutin {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    /* KUNING - TIDAK RUTIN */
    .progres-tidak-rutin {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    /* MERAH - PUTUS OBAT */
    .progres-putus {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    /* ABU-ABU - BELUM ADA DATA */
    .progres-kosong {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
    }
</style>

<!-- HEADER HALAMAN -->
<div class="heading">
    <h1>Monitoring &amp; Tindak Lanjut</h1>
    <p>Pencatatan kegiatan oleh petugas berwenang</p>
</div>

<?php if ($error): ?>
    <div class="alert error">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<!-- FORM TAMBAH MONITORING -->
<section class="panel">
    <div class="panel-title">
        Tambah Monitoring
    </div>

    <div class="panel-body">
        <form method="post" class="form-grid">

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf()) ?>"
            >

            <!-- PASIEN -->
            <div class="field">
                <label>Pasien</label>

                <select name="patient_id" required>
                    <option value="">Pilih pasien</option>

                    <?php foreach ($patients as $r): ?>
                        <option
                            value="<?= (int) $r['id'] ?>"
                            <?= (string) $old['patient_id'] === (string) $r['id'] ? 'selected' : '' ?>
                        >
                            <?= e($r['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- TANGGAL -->
            <div class="field">
                <label>Tanggal</label>

                <input
                    type="date"
                    name="tanggal"
                    value="<?= e($old['tanggal']) ?>"
                    required
                >
            </div>

            <!-- STATUS -->
            <div class="field">
                <label>Status</label>

                <select name="status" required>
                    <?php foreach ($pilihanStatus as $status): ?>
                        <option
                            value="<?= e($status) ?>"
                            <?= $old['status'] === $status ? 'selected' : '' ?>
                        >
                            <?= e($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- PROGRES PASIEN -->
            <div class="field">
                <label>Progres Pasien</label>

                <select name="progres_pasien" required>
                    <option value="">
                        -- Pilih Progres Pasien --
                    </option>

                    <?php foreach ($pilihanProgres as $progres): ?>
                        <option
                            value="<?= e($progres) ?>"
                            <?= $old['progres_pasien'] === $progres ? 'selected' : '' ?>
                        >
                            <?= e($progres) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- TINDAK LANJUT -->
            <div class="field full">
                <label>Tindak Lanjut</label>

                <textarea
                    name="tindak_lanjut"
                    rows="3"
                ><?= e($old['tindak_lanjut']) ?></textarea>
            </div>

            <button type="submit" class="btn">
                Simpan Monitoring
            </button>

        </form>
    </div>
</section>

<!-- RIWAYAT MONITORING -->
<section class="panel">
    <div class="panel-title">
        Riwayat Monitoring
    </div>

    <div class="panel-body table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Pasien</th>
                    <th>Status</th>
                    <th>Progres Pasien</th>
                    <th>Tindak Lanjut</th>
                    <th>Petugas</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">
                            Belum ada data monitoring.
                        </td>
                    </tr>
                <?php else: ?>

                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <!-- TANGGAL -->
                            <td>
                                <?= e($r['tanggal']) ?>
                            </td>

                            <!-- PASIEN -->
                            <td>
                                <?= e($r['pasien']) ?>
                            </td>

                            <!-- STATUS -->
                            <td>
                                <span class="badge">
                                    <?= e($r['status']) ?>
                                </span>
                            </td>

                            <!-- PROGRES PASIEN DENGAN WARNA -->
                            <td>
                                <?php
                                $progres = $r['progres_pasien'] ?? '';

                                switch ($progres) {
                                    case 'Rutin Mengonsumsi Obat':
                                        $warna = 'progres-rutin';
                                        break;

                                    case 'Tidak Rutin':
                                        $warna = 'progres-tidak-rutin';
                                        break;

                                    case 'Putus Obat':
                                        $warna = 'progres-putus';
                                        break;

                                    default:
                                        $warna = 'progres-kosong';
                                        break;
                                }
                                ?>

                                <span class="badge-progres <?= e($warna) ?>">
                                    <?= e(
                                        $progres !== ''
                                            ? $progres
                                            : 'Belum Ada Data'
                                    ) ?>
                                </span>
                            </td>

                            <!-- TINDAK LANJUT -->
                            <td>
                                <?= e($r['tindak_lanjut'] ?? '') ?>
                            </td>

                            <!-- PETUGAS -->
                            <td>
                                <?= e($r['petugas'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include 'partials/footer.php'; ?>
