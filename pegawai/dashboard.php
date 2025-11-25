<?php
include '../config.php';
if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Query untuk draft pengajuan user
    $sql_draft = "
        SELECT pc.*, jc.nama_jenis, d.nama_departemen
        FROM pengajuan_cuti pc 
        JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
        JOIN users u ON pc.user_id = u.id
        JOIN departemen d ON u.departemen_id = d.id
        WHERE pc.user_id = :user_id AND pc.status = 'draft'
        ORDER BY pc.dibuat_pada DESC
    ";
    $stmt_draft = $pdo->prepare($sql_draft);
    $stmt_draft->bindValue(':user_id', $user_id);
    $stmt_draft->execute();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pegawai - Sistem Cuti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            font-weight: 600;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }

        .btn-group-sm>.btn,
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .welcome-card {
            background-color: white;
            border: none;
            border-radius: 8px;
        }

        .action-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .user-info {
            font-size: 0.9rem;
        }

        .draft-item {
            border-left: 4px solid #6f42c1;
            padding-left: 15px;
            margin-bottom: 20px;
            background-color: white;
            padding: 15px;
            border-radius: 4px;
        }

        .draft-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .draft-detail {
            margin-bottom: 3px;
            color: #666;
        }

        .btn-create {
            background-color: #000;
            border-color: #000;
            color: white;
            padding: 12px 18px;
            font-size: 1.15rem;
            font-weight: 700;
            border-radius: 8px;
        }

        .btn-create:hover {
            background-color: #111;
            border-color: #111;
        }

        .btn-history {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
            padding: 10px 14px;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-history:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        hr {
            border-top: 2px solid #dee2e6;
            margin: 1.5rem 0;
        }

        .button-container {
            max-width: 980px;
            margin: 0 auto 20px auto;
            width: 100%;
            padding: 0 15px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .button-row {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
        }

        /* Make create button larger than history button */
        .button-container .btn-create {
            flex: 3 1 0;
        }

        .button-container .btn-history {
            flex: 1 1 0;
            max-width: 160px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Sistem Cuti</a>
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <span class="navbar-text me-3 user-info">
                    Halo <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong> – Pegawai
                </span>
                <a class="nav-link text-white" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Button Container -->
        <div class="button-container">
            <a href="pengajuan_cuti.php" class="btn btn-create">+ Buat Pengajuan</a>
            <a href="history.php" class="btn btn-history">Histori</a>
        </div>

        <hr>

        <!-- Draft Pengajuan -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Draft Pengajuan Saya:</h5>
            </div>
            <div class="card-body">
                <?php if ($stmt_draft->rowCount() > 0): ?>
                    <?php while ($row = $stmt_draft->fetch(PDO::FETCH_ASSOC)):
                        // Hitung jumlah hari
                        $tanggal_mulai = new DateTime($row['tanggal_mulai']);
                        $tanggal_selesai = new DateTime($row['tanggal_selesai']);
                        $jumlah_hari = $tanggal_mulai->diff($tanggal_selesai)->days + 1;
                    ?>
                        <div class="draft-item">
                            <div class="draft-title"><?php echo htmlspecialchars($row['nama_jenis']); ?></div>
                            <div class="draft-detail">Tanggal mulai: <?php echo htmlspecialchars($row['tanggal_mulai']); ?></div>
                            <div class="draft-detail">Tanggal selesai: <?php echo htmlspecialchars($row['tanggal_selesai']); ?></div>
                            <div class="draft-detail">Jumlah hari: <?php echo $jumlah_hari; ?> Hari</div>
                            <div class="mt-3">
                                <a href="submit_pengajuan.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Ajukan cuti ini?')">Kirim</a>
                                <a href="edit_pengajuan.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_pengajuan.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus pengajuan?')">Hapus</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Tidak ada draft pengajuan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>