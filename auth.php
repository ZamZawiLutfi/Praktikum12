<?php
session_start();

if (isset($_SESSION['nama'])) {
    header("Location: dashboard.php");
    exit();
}

require 'koneksi.php';
$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['register'])) {
        $nama     = trim($_POST['nama']);
        $password = $_POST['password'];

        if (empty($nama) || empty($password)) {
            $pesan = "❌ Validasi Gagal: Nama dan password wajib diisi.";
        } elseif (strlen($password) < 6) {
            $pesan = "❌ Validasi Gagal: Password minimal 6 karakter.";
        } else {
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE nama = ?");
            $stmt_check->bind_param("s", $nama);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $pesan = "❌ Registrasi Gagal: Nama sudah terdaftar.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (nama, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $nama, $hashed_password);

                if ($stmt->execute()) {
                    $pesan = "✅ Registrasi Berhasil! Silakan login.";
                } else {
                    $pesan = "❌ Kesalahan Server: " . $stmt->error;
                }
                $stmt->close();
            }
            $stmt_check->close();
        }
    }

    if (isset($_POST['login'])) {
        $nama     = trim($_POST['nama']);
        $password = $_POST['password'];

        if (empty($nama) || empty($password)) {
            $pesan = "❌ Validasi Gagal: Nama dan password wajib diisi.";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE nama = ?");
            $stmt->bind_param("s", $nama);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['nama'] = $nama;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $pesan = "❌ Login Gagal: Password salah.";
                }
            } else {
                $pesan = "❌ Login Gagal: Pengguna tidak ditemukan.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }

        h1 { font-size: 1.6rem; color: #1a1a2e; margin-bottom: 6px; }
        .subtitle { color: #666; font-size: 0.9rem; margin-bottom: 24px; }

        .pesan {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #333;
        }
        .pesan.sukses { background: #d4edda; border-color: #28a745; }
        .pesan.error  { background: #f8d7da; border-color: #dc3545; }

        .tabs {
            display: flex;
            margin-bottom: 24px;
            border-bottom: 2px solid #e0e0e0;
        }
        .tab-btn {
            flex: 1;
            padding: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            color: #888;
            font-weight: 600;
            transition: all 0.2s;
        }
        .tab-btn.active {
            color: #4f46e5;
            border-bottom: 2px solid #4f46e5;
            margin-bottom: -2px;
        }

        .form-section { display: none; }
        .form-section.active { display: block; }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-bottom: 16px;
            transition: border 0.2s;
            outline: none;
        }
        input:focus { border-color: #4f46e5; }

        button[type="submit"] {
            width: 100%;
            padding: 11px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover { background: #3730a3; }
    </style>
</head>
<body>
<div class="container">
    <h1>Sistem Login</h1>
    <p class="subtitle">Pertemuan 12 — Pemrograman Web</p>

    <?php if ($pesan !== ""): ?>
        <?php
            $kelas = "pesan";
            if (str_contains($pesan, "✅")) $kelas .= " sukses";
            elseif (str_contains($pesan, "❌")) $kelas .= " error";
        ?>
        <div class="<?= $kelas ?>"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('register', this)">Register</button>
        <button class="tab-btn"        onclick="switchTab('login', this)">Login</button>
    </div>

    <!-- FORM REGISTRASI -->
    <div id="tab-register" class="form-section active">
        <form method="POST" action="">
            <label>Nama Pengguna</label>
            <input type="text" name="nama" placeholder="Masukkan nama" required>

            <label>Password <small style="color:#999">(min. 6 karakter)</small></label>
            <input type="password" name="password" placeholder="Buat password" required>

            <button type="submit" name="register">Daftar Sekarang</button>
        </form>
    </div>

    <div id="tab-login" class="form-section">
        <form method="POST" action="">
            <label>Nama Pengguna</label>
            <input type="text" name="nama" placeholder="Masukkan nama" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>

            <button type="submit" name="login">Masuk</button>
        </form>
    </div>
</div>

<script>
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + tab).classList.add('active');
    }

    // Kalau ada pesan error login, langsung buka tab login
    <?php if ($pesan !== "" && str_contains($pesan, "Login")): ?>
        switchTab('login', document.querySelectorAll('.tab-btn')[1]);
    <?php endif; ?>
</script>
</body>
</html>
