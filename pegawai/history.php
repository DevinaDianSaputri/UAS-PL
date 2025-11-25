<?php
include '../config.php';
if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Query untuk history pengajuan user (selain draft)
    $sql = "
        SELECT pc.*, jc.nama_jenis, d.nama_departemen,
               u_admin.nama_lengkap as admin_nama
        FROM pengajuan_cuti pc 
        JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
        JOIN users u ON pc.user_id = u.id
        JOIN departemen d ON u.departemen_id = d.id
        LEFT JOIN users u_admin ON pc.admin_id = u_admin.id
        WHERE pc.user_id = :user_id AND pc.status != 'draft'
        ORDER BY pc.dibuat_pada DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Pengajuan - Sistem Cuti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        .table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <span class="navbar-text me-3">
                    Halo <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>
                </span>
                <a class="nav-link text-white" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>History Pengajuan Cuti</h2>
            <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if ($stmt->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Jumlah Hari</th>
                                    <th>Status</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Disetujui Oleh</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                                    $tanggal_mulai = new DateTime($row['tanggal_mulai']);
                                    $tanggal_selesai = new DateTime($row['tanggal_selesai']);
                                    $jumlah_hari = $tanggal_mulai->diff($tanggal_selesai)->days + 1;
                                    
                                    // Tentukan badge color berdasarkan status
                                    $badge_color = '';
                                    switch($row['status']) {
                                        case 'pending': $badge_color = 'bg-warning'; break;
                                        case 'disetujui': $badge_color = 'bg-success'; break;
                                        case 'ditolak': $badge_color = 'bg-danger'; break;
                                        default: $badge_color = 'bg-secondary';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nama_jenis']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tanggal_mulai']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tanggal_selesai']); ?></td>
                                        <td><?php echo $jumlah_hari; ?> Hari</td>
                                        <td>
                                            <span class="badge <?php echo $badge_color; ?> status-badge">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['dibuat_pada'])); ?></td>
                                        <td><?php echo $row['admin_nama'] ? htmlspecialchars($row['admin_nama']) : '-'; ?></td>
                                        <td>
                                            <?php if ($row['alasan_admin']): ?>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        data-bs-toggle="tooltip" 
                                                        title="<?php echo htmlspecialchars($row['alasan_admin']); ?>">
                                                    Lihat
                                                </button>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Belum ada history pengajuan cuti.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>