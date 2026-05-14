<?php
session_start();

if (!isset($_SESSION['nama']) || $_SESSION['nama'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

require 'koneksi.php';

$id    = (int) $_GET['id'];
$pesan = "";

$stmt_get = $conn->prepare("SELECT id, nama FROM users WHERE id = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$result = $stmt_get->get_result();
$user   = $result->fetch_assoc();
$stmt_get->close();

if (!$user) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['simpan'])) {
    $nama_baru     = trim($_POST['nama']);
    $password_baru = $_POST['password'];

    if (empty($nama_baru)) {
        $pesan = "❌ Nama tidak boleh kosong.";
    } elseif (empty($password_baru)) {
        $pesan = "❌ Password baru tidak boleh kosong.";
    } elseif (strlen($password_baru) < 6) {
        $pesan = "❌ Password minimal 6 karakter.";
    } else {

        $stmt_cek = $conn->prepare("SELECT id FROM users WHERE nama = ? AND id != ?");
        $stmt_cek->bind_param("si", $nama_baru, $id);
        $stmt_cek->execute();
        $stmt_cek->store_result();

        if ($stmt_cek->num_rows > 0) {
            $pesan = "❌ Nama pengguna sudah dipakai oleh akun lain.";
        } else {
            $hashed_baru = password_hash($password_baru, PASSWORD_BCRYPT);

            $stmt_update = $conn->prepare("UPDATE users SET nama = ?, password = ? WHERE id = ?");
            $stmt_update->bind_param("ssi", $nama_baru, $hashed_baru, $id);

            if ($stmt_update->execute()) {
                header("Location: dashboard.php");
                exit();
            } else {
                $pesan = "❌ Kesalahan Server: " . $stmt_update->error;
            }
            $stmt_update->close();
        }
        $stmt_cek->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna</title>
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

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 36px 40px;
            width: 100%;
            max-width: 440px;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
        }
        .card-header h2 { font-size: 1.2rem; color: #1a1a2e; }
        .id-badge {
            margin-left: auto;
            background: #eef2ff;
            color: #4f46e5;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .pesan {
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 0.88rem;
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #7f1d1d;
        }

        label {
            display: block;
            font-size: 0.83rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-bottom: 18px;
            outline: none;
            transition: border 0.2s;
        }
        input:focus { border-color: #4f46e5; }

        .btn-group { display: flex; gap: 10px; margin-top: 4px; }

        .btn-simpan {
            flex: 1;
            padding: 11px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-simpan:hover { background: #3730a3; }

        .btn-batal {
            padding: 11px 20px;
            background: #f3f4f6;
            color: #374151;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .btn-batal:hover { background: #e5e7eb; }

        .hint {
            font-size: 0.78rem;
            color: #9ca3af;
            margin-top: -14px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h2>✏️ Edit Data Pengguna</h2>
        <span class="id-badge">ID #<?= $user['id'] ?></span>
    </div>

    <?php if ($pesan !== ""): ?>
        <div class="pesan"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nama Pengguna</label>
        <input type="text" name="nama"
               value="<?= htmlspecialchars($user['nama']) ?>"
               required>

        <label>Password Baru</label>
        <input type="password" name="password"
               placeholder="Masukkan password baru"
               required>
        <p class="hint">⚠️ Password akan di-hash ulang secara otomatis.</p>

        <div class="btn-group">
            <button type="submit" name="simpan" class="btn-simpan">Simpan Perubahan</button>
            <a href="dashboard.php" class="btn-batal">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
