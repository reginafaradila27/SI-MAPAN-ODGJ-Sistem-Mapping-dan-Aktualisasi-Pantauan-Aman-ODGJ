
<?php
require 'config/db.php';

if (!empty($_SESSION['uid'])) {
    redirect('index.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $s = $pdo->prepare(
        'SELECT * FROM users WHERE username = ?'
    );

    $s->execute([$username]);
    $u = $s->fetch();

    if ($u && password_verify($password, $u['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['uid'] = $u['id'];
        $_SESSION['nama'] = $u['nama'];
        $_SESSION['role'] = $u['role'];

        redirect('index.php');
        exit;
    }

    $error = 'Username atau password salah.';
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login SI-MAPAN ODGJ</title>

    <link rel="stylesheet" href="assets/style.css">

    <style>
        /* BACKGROUND FOTO DINAS KESEHATAN */
        .login-left {
            background:
                linear-gradient(
                    135deg,
                    rgba(7, 35, 65, 0.86),
                    rgba(12, 75, 110, 0.75)
                ),
                url('assets/dinkes.webp') center center / cover no-repeat;
        }

        /* Konten tetap berada di atas dekorasi */
        .login-left > div {
            position: relative;
            z-index: 2;
        }

        /* Menghilangkan dekorasi lengkung lama */
        .login-left::after {
            display: none;
        }

        /* TOMBOL LIHAT / SEMBUNYIKAN PASSWORD */
        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 50px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            border-radius: 5px;
        }

        .toggle-password:hover {
            color: #008c90;
            background: #edf8f8;
        }

        .toggle-password:focus-visible {
            outline: 2px solid #008c90;
            outline-offset: 2px;
        }

        .toggle-password svg {
            width: 21px;
            height: 21px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
    </style>
</head>

<body class="login-page">

    <!-- BAGIAN KIRI -->
    <section class="login-left">
        <div>

            <div class="brain">✚</div>

            <h1>SI-MAPAN ODGJ</h1>

            <p>
                Sistem Mapping dan Aktualisasi<br>
                Pantauan Aman ODGJ
            </p>

            <hr style="
                width:110px;
                border:4px solid #1cc4cb;
                margin:25px 0;
            ">

            <ul>
                <li>Pemetaan ODGJ</li>
                <li>Pemantauan Kondisi</li>
                <li>Pelaporan &amp; Evaluasi Pasien</li>
            </ul>

        </div>
    </section>

    <!-- BAGIAN KANAN -->
    <section class="login-right">

        <div class="login-card">

            <div style="font-size:55px">⚕️</div>

            <h1>SI-MAPAN ODGJ</h1>

            <p>
                Sistem Mapping dan Aktualisasi<br>
                Pantauan Aman ODGJ
            </p>

            <h3>Login Pengguna</h3>

            <?php if ($error): ?>
                <div class="alert error">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['setup'])): ?>
                <div class="alert">
                    Admin berhasil dibuat. Silakan login.
                </div>
            <?php endif; ?>

            <form method="post">

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= e(csrf()) ?>"
                >

                <!-- USERNAME -->
                <div class="field">
                    <label for="username">Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Username"
                        required
                        autocomplete="username"
                        value="<?= e($username) ?>"
                    >
                </div>

                <!-- PASSWORD -->
                <div class="field">
                    <label for="password">Password</label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Password"
                            required
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            id="togglePassword"
                            aria-label="Tampilkan password"
                            aria-pressed="false"
                            title="Tampilkan password"
                        >
                            <svg
                                id="eyeIcon"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path d="
                                    M2 12
                                    S5.5 5 12 5
                                    S22 12 22 12
                                    S18.5 19 12 19
                                    S2 12 2 12
                                " />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>

                    </div>
                </div>

                <!-- TOMBOL MASUK -->
                <button type="submit" class="btn">
                    ➜ Masuk
                </button>

            </form>

            <p
                class="muted"
                style="font-size:12px;margin-top:35px"
            >
                Dinas Kesehatan Kota Banjarmasin
                · Akses terbatas
            </p>

        </div>
    </section>

    <!-- SCRIPT LIHAT / SEMBUNYIKAN PASSWORD -->
    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        const eyeNormal = `
            <path d="M2 12 S5.5 5 12 5 S22 12 22 12
                     S18.5 19 12 19 S2 12 2 12" />
            <circle cx="12" cy="12" r="3" />
        `;

        const eyeClosed = `
            <path d="M3 3 L21 21" />
            <path d="M10.6 5.1 A11 11 0 0 1 12 5
                     C18.5 5 22 12 22 12
                     A17 17 0 0 1 18.5 16.5" />
            <path d="M6.2 6.2 C3.5 8 2 12 2 12
                     S5.5 19 12 19
                     C13.5 19 15 18.7 16.3 18" />
        `;

        togglePassword.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';

            passwordInput.type = isHidden ? 'text' : 'password';

            togglePassword.setAttribute(
                'aria-label',
                isHidden ? 'Sembunyikan password' : 'Tampilkan password'
            );

            togglePassword.setAttribute(
                'aria-pressed',
                isHidden ? 'true' : 'false'
            );

            togglePassword.title = isHidden
                ? 'Sembunyikan password'
                : 'Tampilkan password';

            eyeIcon.innerHTML = isHidden ? eyeClosed : eyeNormal;
        });
    </script>

</body>
</html>
