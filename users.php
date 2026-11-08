
<?php
require_once 'config/db.php';

auth();
admin();

$error = '';
$success = '';

$name = '';
$username = '';
$role = 'dinkes';

// PROSES TAMBAH AKUN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $name = trim((string)($_POST['nama'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = (string)($_POST['role'] ?? '');

    // VALIDASI INPUT
    if (
        mb_strlen($name) < 3 ||
        strlen($username) < 3 ||
        strlen($password) < 12 ||
        !in_array($role, ['admin', 'dinkes', 'puskesmas'], true)
    ) {
        $error = 'Data tidak valid. Nama dan username minimal 3 karakter, password minimal 12 karakter.';
    } else {

        // CEK USERNAME SUDAH ADA ATAU BELUM
        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE username = ?"
        );
        $check->execute([$username]);

        if ($check->fetchColumn() > 0) {

            $error = 'Username sudah digunakan. Silakan pilih username lain.';

        } else {

            // HASH PASSWORD
            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            try {

                // SIMPAN AKUN TANPA PUSKESMAS_ID
                $sql = "
                    INSERT INTO users (
                        nama,
                        username,
                        password_hash,
                        role
                    ) VALUES (?, ?, ?, ?)
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $name,
                    $username,
                    $passwordHash,
                    $role
                ]);

                redirect('users.php?success=1');

            } catch (PDOException $ex) {

                $error = 'Gagal membuat akun. Periksa data yang dimasukkan.';

            }
        }
    }
}

// NOTIFIKASI BERHASIL
if (($_GET['success'] ?? '') === '1') {
    $success = 'Akun pengguna berhasil ditambahkan.';
}

// AMBIL DAFTAR PENGGUNA
$stmt = $pdo->query("
    SELECT
        id,
        nama,
        username,
        role
    FROM users
    ORDER BY id DESC
");

$rows = $stmt->fetchAll();

include 'partials/header.php';
?>

<div class="heading">
    <h1>Manajemen Pengguna</h1>
    <p>Pengelolaan akun pengguna SI-MAPAN ODGJ</p>
</div>

<?php if ($error): ?>
    <div class="alert error">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert success">
        <?= e($success) ?>
    </div>
<?php endif; ?>

<!-- FORM TAMBAH PENGGUNA -->
<section class="panel">

    <div class="panel-title">
        Tambah Akun Pengguna
    </div>

    <div class="panel-body">

        <form method="post" class="form-grid">

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf()) ?>"
            >

            <div class="field">
                <label>Nama Lengkap *</label>
                <input
                    type="text"
                    name="nama"
                    value="<?= e($name) ?>"
                    minlength="3"
                    maxlength="150"
                    required
                >
            </div>

            <div class="field">
                <label>Username *</label>
                <input
                    type="text"
                    name="username"
                    value="<?= e($username) ?>"
                    minlength="3"
                    maxlength="100"
                    autocomplete="off"
                    required
                >
            </div>

            <div class="field">
                <label>Password (minimal 12 karakter) *</label>
                <input
                    type="password"
                    name="password"
                    minlength="12"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="field">
                <label>Role / Hak Akses *</label>

                <select name="role" required>

                    <option value="dinkes"
                        <?= $role === 'dinkes' ? 'selected' : '' ?>>
                        Petugas Dinas Kesehatan
                    </option>

                    <option value="puskesmas"
                        <?= $role === 'puskesmas' ? 'selected' : '' ?>>
                        Petugas Puskesmas
                    </option>

                    <option value="admin"
                        <?= $role === 'admin' ? 'selected' : '' ?>>
                        Administrator
                    </option>

                </select>
            </div>

            <div class="field">
                <label>&nbsp;</label>
                <button type="submit" class="btn">
                    + Buat Akun
                </button>
            </div>

        </form>

    </div>
</section>

<!-- DAFTAR AKUN -->
<section class="panel">

    <div class="panel-title">
        Akun Pengguna Terdaftar
    </div>

    <div class="panel-body table-wrap">

        <table>

            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Role</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($rows as $i => $r): ?>

                    <tr>
                        <td><?= $i + 1 ?></td>

                        <td><?= e($r['nama']) ?></td>

                        <td><?= e($r['username']) ?></td>

                        <td>
                            <?php
                            $roleLabels = [
                                'admin' => 'Administrator',
                                'dinkes' => 'Petugas Dinas Kesehatan',
                                'puskesmas' => 'Petugas Puskesmas'
                            ];

                            echo e(
                                $roleLabels[$r['role']]
                                ?? $r['role']
                            );
                            ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

                <?php if (!$rows): ?>

                    <tr>
                        <td colspan="4" class="muted">
                            Belum ada akun pengguna terdaftar.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>
</section>

<?php include 'partials/footer.php'; ?>
