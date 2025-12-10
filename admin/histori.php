<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Ambil semua pengajuan dengan data approval
$sql = "SELECT pc.*, u.nama_lengkap, u.nip, d.nama_departemen, jc.nama_jenis 
        FROM pengajuan_cuti pc 
        JOIN users u ON pc.id_user = u.id_user 
        JOIN departemen d ON u.id_departemen = d.id_departemen
        JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
        WHERE pc.status IN ('disetujui', 'ditolak')
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
            padding: 0.35em 0.65em;
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

        .badge-draft {
            background-color: #6c757d;
        }
        
        .alasan-preview {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .alasan-admin-preview {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #666;
            font-style: italic;
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
                <a class="nav-link" href="jenis_cuti/jenis_cuti.php">Jenis Cuti</a>
                <a class="nav-link" href="departemen/departemen.php">Departemen</a>
                <a class="nav-link" href="karyawan/karyawan.php">Karyawan</a>
                <a class="nav-link active" href="histori.php">Histori</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Histori Semua Pengajuan Cuti</h5>
                <span class="badge bg-primary"><?php echo count($pengajuan); ?> Total Pengajuan</span>
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
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Info Approval</th>
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
                                        case 'draft':
                                            $badge_class = 'badge-draft';
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
                                        <td>
                                            <span class="alasan-preview" title="<?php echo htmlspecialchars($row['alasan']); ?>">
                                                <?php 
                                                $alasan = htmlspecialchars($row['alasan']);
                                                echo strlen($alasan) > 30 ? substr($alasan, 0, 30) . '...' : $alasan;
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge status-badge <?php echo $badge_class; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'disetujui' || $row['status'] == 'ditolak'): ?>
                                                <div class="small">
                                                    <div><?php echo $row['tanggal_disetujui'] ? date('d/m/Y H:i', strtotime($row['tanggal_disetujui'])) : '-'; ?></div>
                                                    <?php if ($row['alasan_admin']): ?>
                                                        <div class="alasan-admin-preview" title="<?php echo htmlspecialchars($row['alasan_admin']); ?>">
                                                            <?php 
                                                            $alasan_admin = htmlspecialchars($row['alasan_admin']);
                                                            echo strlen($alasan_admin) > 30 ? substr($alasan_admin, 0, 30) . '...' : $alasan_admin;
                                                            ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif ($row['status'] == 'pending'): ?>
                                                <span class="text-muted">Menunggu</span>
                                            <?php else: ?>
                                                <span class="text-muted">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="dashboard.php" class="btn btn-primary mt-2">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h5 class="text-muted mt-3">Belum ada histori pengajuan</h5>
                        <p class="text-muted">Semua pengajuan cuti akan muncul di sini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</body>

</html>