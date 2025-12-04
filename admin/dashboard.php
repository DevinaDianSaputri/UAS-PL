<?php
session_start(); // Tambahkan ini
include '../config.php';
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

try {
    // Query untuk pengajuan yang belum diproses (pending)
    $sql_pending = "
        SELECT pc.*, u.nama_lengkap, u.nip, u.email, d.nama_departemen, jc.nama_jenis 
        FROM pengajuan_cuti pc 
        JOIN users u ON pc.id_user = u.id_user 
        JOIN departemen d ON u.id_departemen = d.id_departemen
        JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
        WHERE pc.status = 'pending'
        ORDER BY pc.dibuat_pada DESC
    ";
    $stmt_pending = $pdo->prepare($sql_pending);
    $stmt_pending->execute();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Cuti</title>
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

        .user-info {
            font-size: 0.9rem;
        }

        .action-buttons .btn {
            margin-right: 0.3rem;
            margin-bottom: 0.3rem;
        }

        .menu-card {
            cursor: pointer;
            transition: transform 0.2s;
            height: 100%;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .menu-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <span class="navbar-text me-3 user-info">
                    Halo <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong> – Admin
                </span>
                <a class="nav-link text-white" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Notifikasi -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Menu Utama -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <a href="jenis_cuti.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <div class="menu-icon">📋</div>
                        <h5>Jenis Cuti</h5>
                        <p class="text-muted">Kelola jenis cuti</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="departemen.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <div class="menu-icon">🏢</div>
                        <h5>Departemen</h5>
                        <p class="text-muted">Kelola departemen</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="karyawan.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <div class="menu-icon">👥</div>
                        <h5>Karyawan</h5>
                        <p class="text-muted">Kelola data karyawan</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="histori.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <div class="menu-icon">📊</div>
                        <h5>Histori</h5>
                        <p class="text-muted">Lihat semua pengajuan</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Pengajuan Menunggu Persetujuan -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pengajuan Menunggu Persetujuan</h5>
                <span class="badge bg-warning"><?php echo $stmt_pending->rowCount(); ?> Pengajuan</span>
            </div>
            <div class="card-body">
                <?php if ($stmt_pending->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIP</th>
                                    <th>Departemen</th>
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = $stmt_pending->fetch(PDO::FETCH_ASSOC)):
                                    $tanggal_mulai = new DateTime($row['tanggal_mulai']);
                                    $tanggal_selesai = new DateTime($row['tanggal_selesai']);
                                    $jumlah_hari = $tanggal_mulai->diff($tanggal_selesai)->days + 1;
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nip']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_departemen']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_jenis']); ?></td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?><br>
                                            <small>s/d <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?></small>
                                        </td>
                                        <td><?php echo $jumlah_hari; ?> hari</td>
                                        <td>
                                            <span class="badge bg-warning status-badge">Pending</span>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="process_pengajuan.php?id=<?php echo $row['id_pengajuan']; ?>&action=approve" 
                                               class="btn btn-success btn-sm" 
                                               onclick="return confirm('Setujui pengajuan ini?')">
                                                Setujui
                                            </a>
                                            <a href="process_pengajuan.php?id=<?php echo $row['id_pengajuan']; ?>&action=reject" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Tolak pengajuan ini?')">
                                                Tolak
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Tidak ada pengajuan yang menunggu persetujuan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>