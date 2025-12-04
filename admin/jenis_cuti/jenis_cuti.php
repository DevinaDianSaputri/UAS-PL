<?php
session_start();
include '../../config.php';

// Cek login menggunakan fungsi dari config.php
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../../login.php");
    exit();
}

// Ambil data jenis cuti
$sql = "SELECT * FROM jenis_cuti ORDER BY id_jenis DESC";
$stmt = $pdo->query($sql);
$jenis_cuti = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jenis Cuti - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); border: none; margin-bottom: 20px; }
        .card-header { background-color: #fff; border-bottom: 1px solid rgba(0,0,0,0.125); font-weight: 600; }
        .table th { font-weight: 600; background-color: #f8f9fa; }
        .btn-action { margin: 2px; }
        .alert { border: none; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3 text-white">
                    Admin: <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?>
                </span>
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link active" href="jenis_cuti.php">Jenis Cuti</a>
                <a class="nav-link" href="../departemen/departemen.php">Departemen</a>
                <a class="nav-link" href="../karyawan/karyawan.php">Karyawan</a>
                <a class="nav-link" href="../histori.php">Histori</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Notifikasi -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kelola Jenis Cuti</h5>
                <a href="tambah_jenis.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Jenis Cuti
                </a>
            </div>
            <div class="card-body">
                <?php if (count($jenis_cuti) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Jenis Cuti</th>
                                    <th>Maksimal Hari</th>
                                    <th>Deskripsi</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($jenis_cuti as $jenis): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($jenis['nama_jenis']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $jenis['maks_hari']; ?> hari
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($jenis['deskripsi_cuti'] ?? '-'); ?></td>
                                        <td>
                                            <a href="edit_jenis.php?id=<?php echo $jenis['id_jenis']; ?>" 
                                               class="btn btn-warning btn-sm btn-action">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="hapus_jenis.php?id=<?php echo $jenis['id_jenis']; ?>" 
                                               class="btn btn-danger btn-sm btn-action"
                                               onclick="return confirm('Hapus jenis cuti <?php echo addslashes($jenis['nama_jenis']); ?>?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-clipboard-x" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h5 class="text-muted mt-3">Belum ada jenis cuti</h5>
                        <p class="text-muted">Silakan tambahkan jenis cuti baru</p>
                        <a href="tambah_jenis.php" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Tambah Jenis Cuti
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>