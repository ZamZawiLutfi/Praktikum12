<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header("Location: auth.php");
    exit();
}

require 'koneksi.php';

$nama_login = $_SESSION['nama'];
$is_admin   = ($nama_login === 'admin');
$pesan      = "";

if ($is_admin && isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];

    $stmt_self = $conn->prepare("SELECT nama FROM users WHERE id = ?");
    $stmt_self->bind_param("i", $id_hapus);
    $stmt_self->execute();
    $stmt_self->bind_result($nama_target);
    $stmt_self->fetch();
    $stmt_self->close();

    if ($nama_target === 'admin') {
        $pesan = "⚠️ Akun admin tidak bisa dihapus.";
    } else {
        $stmt_hapus = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt_hapus->bind_param("i", $id_hapus);
        $stmt_hapus->execute();
        $stmt_hapus->close();
        $pesan = "✅ Pengguna berhasil dihapus.";
    }
}

$users = [];
if ($is_admin) {
    $result = $conn->query("SELECT id, nama FROM users ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        nav {
            background: #1a1a2e;
            color: #fff;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        nav .brand { font-weight: 700; font-size: 1.1rem; letter-spacing: .5px; }
        nav .user-info { display: flex; align-items: center; gap: 14px; font-size: 0.9rem; }
        nav .badge {
            background: <?= $is_admin ? '#f59e0b' : '#4f46e5' ?>;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            background: #dc2626;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background 0.2s;
        }
        nav a:hover { background: #b91c1c; }

        .wrapper { max-width: 900px; margin: 36px auto; padding: 0 20px; }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 28px 32px;
            margin-bottom: 24px;
        }

        .welcome h2 { font-size: 1.5rem; color: #1a1a2e; }
        .welcome p  { color: #666; margin-top: 6px; font-size: 0.95rem; }

        .pesan {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .pesan.sukses { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .pesan.warn   { background: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; }

        .admin-section h3 {
            font-size: 1.1rem;
            color: #1a1a2e;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #1a1a2e; color: #fff; }
        thead th { padding: 11px 16px; text-align: left; font-size: 0.85rem; font-weight: 600; }
        tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
        tbody tr:hover { background: #f9fafb; }
        tbody td { padding: 11px 16px; font-size: 0.9rem; color: #374151; }

        .btn-edit, .btn-hapus {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            margin-right: 4px;
            transition: opacity 0.2s;
        }
        .btn-edit  { background: #dbeafe; color: #1d4ed8; }
        .btn-hapus { background: #fee2e2; color: #b91c1c; }
        .btn-edit:hover, .btn-hapus:hover { opacity: 0.75; }

        .user-section .info-box {
            background: #eef2ff;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .avatar {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: #4f46e5;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; font-weight: 700;
        }
        .user-section .info-box p { color: #4338ca; font-size: 0.95rem; }
        .user-section .info-box strong { font-size: 1.1rem; color: #1e1b4b; }
    </style>
</head>
<body>

<nav>
    <span class="brand">📋 Sistem Login</span>
    <div class="user-info">
        <span>Halo, <strong><?= htmlspecialchars($nama_login) ?></strong></span>
        <span class="badge"><?= $is_admin ? 'Admin' : 'User' ?></span>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="wrapper">

    <?php if ($pesan !== ""): ?>
        <?php $kelas = str_contains($pesan, "✅") ? "sukses" : "warn"; ?>
        <div class="pesan <?= $kelas ?>"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <div class="card welcome">
        <h2>Selamat Datang, <?= htmlspecialchars($nama_login) ?>!</h2>
        <p><?= $is_admin ? 'Anda masuk sebagai <strong>Administrator</strong>. Anda dapat mengelola seluruh data pengguna.' : 'Anda masuk sebagai pengguna reguler. Nikmati layanan kami.' ?></p>
    </div>

    <?php if ($is_admin): ?>
    <div class="card admin-section">
        <h3>⚙️ Menu Admin: Kelola Pengguna</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['nama']) ?></td>
                    <td>
                        <a class="btn-edit" href="edit.php?id=<?= $user['id'] ?>">Edit</a>
                        <?php if ($user['nama'] !== 'admin'): ?>
                            <a class="btn-hapus"
                               href="dashboard.php?hapus=<?= $user['id'] ?>"
                               onclick="return confirm('Yakin hapus pengguna ini?')">Hapus</a>
                        <?php else: ?>
                            <span style="font-size:0.78rem;color:#9ca3af;">–</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr><td colspan="3" style="text-align:center;color:#9ca3af;">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <div class="card user-section">
        <div class="info-box">
            <div class="avatar"><?= strtoupper(substr($nama_login, 0, 1)) ?></div>
            <div>
                <strong><?= htmlspecialchars($nama_login) ?></strong>
                <p>Akun pengguna aktif. Selamat datang di dashboard!</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
