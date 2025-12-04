<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Ambil semua pengajuan
$sql = "SELECT pc.*, u.nama_lengkap, u.nip, d.nama_departemen, jc.nama_jenis 
        FROM pengajuan_cuti pc 
        JOIN users u ON pc.id_user = u.id_user 
        JOIN departemen d ON u.id_departemen = d.id_departemen
        JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
        ORDER BY pc.dibuat_pada DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$pengajuan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Pengajuan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .status-badge {
            font-size: 0.75rem;
        }

        .badge-disetujui {
            background-color: #198754;
        }

        .badge-ditolak {
            background-color: #dc3545;
        }

        .badge-pending {
            background-color: #ffc107;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3 text-white">
                    Admin: <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                </span>
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link active" href="history.php">Histori</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Histori Semua Pengajuan Cuti</h5>
            </div>
            <div class="card-body">
                <?php if (count($pengajuan) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIP</th>
                                    <th>Departemen</th>
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal Cuti</th>
                                    <th>Durasi</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                foreach ($pengajuan as $row):
                                    $tanggal_mulai = new DateTime($row['tanggal_mulai']);
                                    $tanggal_selesai = new DateTime($row['tanggal_selesai']);
                                    $jumlah_hari = $tanggal_mulai->diff($tanggal_selesai)->days + 1;

                                    // Tentukan warna badge
                                    $badge_class = '';
                                    switch ($row['status']) {
                                        case 'disetujui':
                                            $badge_class = 'badge-disetujui';
                                            break;
                                        case 'ditolak':
                                            $badge_class = 'badge-ditolak';
                                            break;
                                        case 'pending':
                                            $badge_class = 'badge-pending';
                                            break;
                                        default:
                                            $badge_class = 'bg-secondary';
                                    }
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
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['dibuat_pada'])); ?></td>
                                        <td>
                                            <span class="badge status-badge <?php echo $badge_class; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">Belum ada pengajuan cuti.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>