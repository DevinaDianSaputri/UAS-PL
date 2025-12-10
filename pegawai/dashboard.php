<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil pengajuan draft milik user ini
$sql_draft = "
    SELECT pc.*, jc.nama_jenis 
    FROM pengajuan_cuti pc 
    JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
    WHERE pc.id_user = ? AND pc.status = 'draft'
    ORDER BY pc.dibuat_pada DESC
";
$stmt_draft = $pdo->prepare($sql_draft);
$stmt_draft->execute([$id_user]);
$draft_cuti = $stmt_draft->fetchAll(PDO::FETCH_ASSOC);
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
        .user-info {
            font-size: 0.9rem;
        }
        .action-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header-buttons {
            display: flex;
            gap: 10px;
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
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Header dengan Tombol Action -->
        <div class="action-header">
            <h4>Dashboard Pegawai</h4>
            <div class="header-buttons">
                <a href="histori.php" class="btn btn-info">
                    <i class="bi bi-clock-history"></i> Histori
                </a>
                <a href="pengajuan_cuti.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Buat Pengajuan
                </a>
            </div>
        </div>

        <!-- Draft Pengajuan Saya -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Draft Pengajuan Saya</h5>
                <?php if (!empty($draft_cuti)): ?>
                    <span class="badge bg-secondary"><?php echo count($draft_cuti); ?> Draft</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($draft_cuti)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal</th>
                                    <th>Durasi</th>
                                    <th>Lampiran</th>
                                    <th>Alasan</th>
                                    <th>Dibuat Pada</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($draft_cuti as $draft): 
                                    $tanggal_mulai = new DateTime($draft['tanggal_mulai']);
                                    $tanggal_selesai = new DateTime($draft['tanggal_selesai']);
                                    $jumlah_hari = $tanggal_mulai->diff($tanggal_selesai)->days + 1;
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($draft['nama_jenis']); ?></td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($draft['tanggal_mulai'])); ?><br>
                                            <small>s/d <?php echo date('d/m/Y', strtotime($draft['tanggal_selesai'])); ?></small>
                                        </td>
                                        <td><?php echo $jumlah_hari; ?> hari</td>
                                        <td>
                                            <?php if ($draft['lampiran']): ?>
                                                <a href="../uploads/<?php echo $draft['lampiran']; ?>" 
                                                   target="_blank" class="badge bg-info text-decoration-none badge-file">
                                                    <i class="bi bi-paperclip"></i> Lihat
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="alasan-preview" title="<?php echo htmlspecialchars($draft['alasan']); ?>">
                                                <?php 
                                                $alasan = htmlspecialchars($draft['alasan']);
                                                echo strlen($alasan) > 30 ? substr($alasan, 0, 30) . '...' : $alasan;
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($draft['dibuat_pada'])); ?></td>
                                        <td class="table-actions">
                                            <a href="edit_pengajuan.php?id=<?php echo $draft['id_pengajuan']; ?>" 
                                               class="btn btn-success btn-sm">
                                                <i class="bi bi-send"></i> Kirim
                                            </a>
                                            <a href="edit_pengajuan.php?id=<?php echo $draft['id_pengajuan']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="hapus_draft.php?id=<?php echo $draft['id_pengajuan']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Hapus draft ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-file-earmark-text" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h5 class="text-muted mt-3">Belum ada draft pengajuan</h5>
                        <p class="text-muted">Silakan buat pengajuan cuti baru</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</body>
</html>