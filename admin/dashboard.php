<?php
session_start();
include '../config.php';
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Proses approval/reject jika ada form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id_pengajuan = $_POST['id_pengajuan'];
    $action = $_POST['action']; // 'approve' atau 'reject'
    $alasan_admin = trim($_POST['alasan_admin'] ?? '');

    // Validasi action
    if (!in_array($action, ['approve', 'reject'])) {
        $_SESSION['error'] = "Aksi tidak valid!";
        header("Location: dashboard.php");
        exit();
    }

    // Validasi alasan untuk reject
    if ($action == 'reject' && empty($alasan_admin)) {
        $_SESSION['error'] = "Harap berikan alasan penolakan!";
        header("Location: dashboard.php");
        exit();
    }

    // Update status berdasarkan action
    if ($action == 'approve') {
        $status = 'disetujui';
        $message = "Pengajuan cuti berhasil disetujui!";
        // Untuk approve, alasan boleh kosong
        if (empty($alasan_admin)) {
            $alasan_admin = "Pengajuan cuti telah disetujui.";
        }
    } else {
        $status = 'ditolak';
        $message = "Pengajuan cuti berhasil ditolak!";
    }

    // Update data pengajuan
    $sql = "UPDATE pengajuan_cuti 
            SET status = ?, 
                alasan_admin = ?,
                tanggal_disetujui = NOW()
            WHERE id_pengajuan = ? AND status = 'pending'";
    
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$status, $alasan_admin, $id_pengajuan])) {
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = "Gagal memproses pengajuan!";
    }

    header("Location: dashboard.php");
    exit();
}

try {
    // Query untuk pengajuan yang belum diproses (pending) dengan lampiran
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
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
            color: #0d6efd;
        }

        /* Warna khusus untuk setiap ikon */
        .icon-jenis-cuti {
            color: #28a745; /* Hijau */
        }
        
        .icon-departemen {
            color: #17a2b8; /* Biru cerah */
        }
        
        .icon-karyawan {
            color: #6f42c1; /* Ungu */
        }
        
        .icon-histori {
            color: #dc3545; /* Merah */
        }

        .badge-file {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        
        .table-actions {
            white-space: nowrap;
        }
        
        .alasan-preview {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                <a href="jenis_cuti/jenis_cuti.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <div class="menu-icon icon-jenis-cuti"><i class="bi bi-calendar-check"></i></div>
                        <h5>Jenis Cuti</h5>
                        <p class="text-muted">Atur kategori cuti</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="departemen/departemen.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <div class="menu-icon icon-departemen"><i class="bi bi-diagram-3"></i></div>
                        <h5>Departemen</h5>
                        <p class="text-muted">Kelola struktur organisasi</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="karyawan/karyawan.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <div class="menu-icon icon-karyawan"><i class="bi bi-person-badge"></i></div>
                        <h5>Karyawan</h5>
                        <p class="text-muted">Kelola data pegawai</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="histori.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <div class="menu-icon icon-histori"><i class="bi bi-journal-text"></i></div>
                        <h5>Histori</h5>
                        <p class="text-muted">Riwayat pengajuan cuti</p>
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
                                    <th>Lampiran</th>
                                    <th>Alasan</th>
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
                                            <?php if ($row['lampiran']): ?>
                                                <a href="../uploads/<?php echo $row['lampiran']; ?>" 
                                                   target="_blank" class="badge bg-info text-decoration-none badge-file">
                                                    <i class="bi bi-paperclip"></i> Lihat
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="alasan-preview" title="<?php echo htmlspecialchars($row['alasan']); ?>">
                                                <?php 
                                                $alasan = htmlspecialchars($row['alasan']);
                                                echo strlen($alasan) > 30 ? substr($alasan, 0, 30) . '...' : $alasan;
                                                ?>
                                            </span>
                                        </td>
                                        <td class="table-actions">
                                            <!-- Tombol Setujui -->
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#approveModal<?php echo $row['id_pengajuan']; ?>">
                                                Setujui
                                            </button>
                                            
                                            <!-- Tombol Tolak -->
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal<?php echo $row['id_pengajuan']; ?>">
                                                Tolak
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Setujui -->
                                    <div class="modal fade" id="approveModal<?php echo $row['id_pengajuan']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Setujui Pengajuan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Setujui pengajuan cuti dari:</p>
                                                        <p><strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong></p>
                                                        <p>Jenis: <?php echo htmlspecialchars($row['nama_jenis']); ?></p>
                                                        <p>Tanggal: <?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?> - <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?></p>
                                                        <p>Alasan: <?php echo htmlspecialchars($row['alasan']); ?></p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Komentar (Opsional)</label>
                                                            <textarea class="form-control" name="alasan_admin" rows="3" placeholder="Berikan komentar..."></textarea>
                                                        </div>
                                                        
                                                        <input type="hidden" name="id_pengajuan" value="<?php echo $row['id_pengajuan']; ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-success">Setujui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Tolak -->
                                    <div class="modal fade" id="rejectModal<?php echo $row['id_pengajuan']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Tolak Pengajuan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Tolak pengajuan cuti dari:</p>
                                                        <p><strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong></p>
                                                        <p>Jenis: <?php echo htmlspecialchars($row['nama_jenis']); ?></p>
                                                        <p>Tanggal: <?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?> - <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?></p>
                                                        <p>Alasan: <?php echo htmlspecialchars($row['alasan']); ?></p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" name="alasan_admin" rows="3" placeholder="Berikan alasan penolakan..." required></textarea>
                                                        </div>
                                                        
                                                        <input type="hidden" name="id_pengajuan" value="<?php echo $row['id_pengajuan']; ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Tolak</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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