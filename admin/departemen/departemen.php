<?php
session_start();
include '../../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../../login.php");
    exit();
}

// Ambil data departemen dengan jumlah karyawan
$sql = "SELECT d.*, COUNT(u.id_user) as jumlah_karyawan 
        FROM departemen d 
        LEFT JOIN users u ON d.id_departemen = u.id_departemen 
        GROUP BY d.id_departemen 
        ORDER BY d.id_departemen DESC";
$stmt = $pdo->query($sql);
$departemen = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Departemen - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }

        .badge-karyawan {
            font-size: 0.75rem;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3 text-white">
                    Admin: <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? ''); ?>
                </span>
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link" href="../jenis_cuti/jenis_cuti.php">Jenis Cuti</a>
                <a class="nav-link active" href="departemen.php">Departemen</a>
                <a class="nav-link" href="../karyawan/karyawan.php">Karyawan</a>
                <a class="nav-link" href="../histori.php">Histori</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kelola Departemen</h5>
                <a href="tambah_departemen.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Departemen
                </a>
            </div>
            <div class="card-body">
                <?php if (count($departemen) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Departemen</th>
                                    <th>Jumlah Karyawan</th>
                                    <th width="20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departemen as $index => $dept): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($dept['nama_departemen']); ?></td>
                                        <td>
                                            <span class="badge bg-info badge-karyawan">
                                                <?php echo $dept['jumlah_karyawan']; ?> karyawan
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_departemen.php?id=<?php echo $dept['id_departemen']; ?>"
                                                class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="hapus_departemen.php?id=<?php echo $dept['id_departemen']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Hapus departemen <?php echo addslashes($dept['nama_departemen']); ?>? Karyawan dalam departemen ini akan kehilangan departemennya.')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="../dashboard.php" class="btn btn-primary mt-2">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-building display-1 text-muted"></i>
                        <h5 class="mt-3">Belum ada departemen</h5>
                        <p class="text-muted">Mulai dengan menambahkan departemen baru</p>
                        <a href="tambah_departemen.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Departemen
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>