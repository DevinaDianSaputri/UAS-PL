<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Ambil data karyawan dengan join departemen
$sql = "SELECT u.*, d.nama_departemen 
        FROM users u 
        LEFT JOIN departemen d ON u.id_departemen = d.id_departemen 
        WHERE u.role = 'pegawai' 
        ORDER BY u.id_user DESC";
$stmt = $pdo->query($sql);
$karyawan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data departemen untuk filter
$sql_dept = "SELECT * FROM departemen ORDER BY nama_departemen";
$stmt_dept = $pdo->query($sql_dept);
$departemen = $stmt_dept->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Karyawan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); border: none; }
        .badge-status { font-size: 0.75rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3 text-white">
                    Admin: <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? ''); ?>
                </span>
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="jenis_cuti.php">Jenis Cuti</a>
                <a class="nav-link" href="departemen.php">Departemen</a>
                <a class="nav-link active" href="karyawan.php">Karyawan</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
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

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kelola Karyawan</h5>
                <div>
                    <a href="tambah_karyawan.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus"></i> Tambah Karyawan
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" id="filterDepartemen">
                            <option value="">Semua Departemen</option>
                            <?php foreach ($departemen as $dept): ?>
                                <option value="<?php echo $dept['id_departemen']; ?>">
                                    <?php echo htmlspecialchars($dept['nama_departemen']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if (count($karyawan) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama</th>
                                    <th>NIP</th>
                                    <th>Email</th>
                                    <th>No. Telp</th>
                                    <th>Departemen</th>
                                    <th>Role</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($karyawan as $k): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($k['nama_lengkap']); ?></td>
                                    <td><code><?php echo htmlspecialchars($k['nip']); ?></code></td>
                                    <td><?php echo htmlspecialchars($k['email']); ?></td>
                                    <td><?php echo htmlspecialchars($k['no_telp']); ?></td>
                                    <td>
                                        <?php if ($k['nama_departemen']): ?>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($k['nama_departemen']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($k['role'] == 'admin') ? 'bg-danger' : 'bg-primary'; ?> badge-status">
                                            <?php echo ucfirst($k['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_karyawan.php?id=<?php echo $k['id_user']; ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="hapus_karyawan.php?id=<?php echo $k['id_user']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Hapus karyawan <?php echo addslashes($k['nama_lengkap']); ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people display-1 text-muted"></i>
                        <h5 class="mt-3">Belum ada karyawan</h5>
                        <p class="text-muted">Mulai dengan menambahkan karyawan baru</p>
                        <a href="tambah_karyawan.php" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Tambah Karyawan
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>