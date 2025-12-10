<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil semua pengajuan user ini yang sudah diproses
$sql = "SELECT pc.*, jc.nama_jenis 
        FROM pengajuan_cuti pc 
        JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
        WHERE pc.id_user = ? AND pc.status IN ('disetujui', 'ditolak')
        ORDER BY pc.dibuat_pada DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_user]);
$histori = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Pengajuan - Pegawai</title>
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
        .user-info {
            font-size: 0.9rem;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        .action-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
            font-size: 0.85rem;
        }
        .badge-file {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <span class="navbar-text me-3 user-info text-white">
                    Halo <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong> – Pegawai
                </span>
                <a class="nav-link text-white" href="logout.php">Logout</a>
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

        <!-- Header dengan Tombol Kembali -->
        <div class="action-header">
            <h4>Histori Pengajuan Cuti</h4>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Tabel Histori -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Riwayat Pengajuan</h5>
                <span class="badge bg-primary"><?php echo count($histori); ?> Pengajuan</span>
            </div>
            <div class="card-body">
                <?php if (!empty($histori)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Durasi</th>
                                    <th>Alasan</th>
                                    <th>Lampiran</th>
                                    <th>Status</th>
                                    <th>Tanggal Diproses</th>
                                    <th>Komentar Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($histori as $item): 
                                    $tanggal_mulai = new DateTime($item['tanggal_mulai']);
                                    $tanggal_selesai = new DateTime($item['tanggal_selesai']);
                                    $jumlah_hari = $tanggal_mulai->diff($tanggal_selesai)->days + 1;
                                    
                                    // Tentukan warna badge
                                    $badge_class = ($item['status'] == 'disetujui') ? 'bg-success' : 'bg-danger';
                                    $status_text = ($item['status'] == 'disetujui') ? 'Disetujui' : 'Ditolak';
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($item['nama_jenis']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($item['tanggal_mulai'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($item['tanggal_selesai'])); ?></td>
                                    <td><?php echo $jumlah_hari; ?> hari</td>
                                    <td>
                                        <span class="alasan-preview" title="<?php echo htmlspecialchars($item['alasan']); ?>">
                                            <?php 
                                            $alasan = htmlspecialchars($item['alasan']);
                                            echo strlen($alasan) > 30 ? substr($alasan, 0, 30) . '...' : $alasan;
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($item['lampiran']): ?>
                                            <a href="../uploads/<?php echo $item['lampiran']; ?>" 
                                               target="_blank" class="badge bg-info text-decoration-none badge-file">
                                                <i class="bi bi-paperclip"></i> Lihat
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $badge_class; ?> status-badge">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($item['tanggal_disetujui']): ?>
                                            <?php echo date('d/m/Y H:i', strtotime($item['tanggal_disetujui'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['alasan_admin']): ?>
                                            <span class="alasan-admin-preview" title="<?php echo htmlspecialchars($item['alasan_admin']); ?>">
                                                <?php 
                                                $alasan_admin = htmlspecialchars($item['alasan_admin']);
                                                echo strlen($alasan_admin) > 30 ? substr($alasan_admin, 0, 30) . '...' : $alasan_admin;
                                                ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h5 class="text-muted mt-3">Belum ada histori pengajuan</h5>
                        <p class="text-muted">Pengajuan yang sudah diproses akan muncul di sini</p>
                        <a href="dashboard.php" class="btn btn-primary mt-2">
                            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</body>
</html>