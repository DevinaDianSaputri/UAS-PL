<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id_pengajuan = $_GET['id'];
$id_user = $_SESSION['id_user'];

// Ambil data draft
$sql = "SELECT pc.*, jc.nama_jenis, jc.maks_hari 
        FROM pengajuan_cuti pc 
        JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
        WHERE pc.id_pengajuan = ? AND pc.id_user = ? AND pc.status = 'draft'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pengajuan, $id_user]);
$draft = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$draft) {
    header("Location: dashboard.php");
    exit();
}

// Ambil jenis cuti
$sql_jenis = "SELECT * FROM jenis_cuti ORDER BY nama_jenis";
$stmt_jenis = $pdo->query($sql_jenis);
$jenis_cuti = $stmt_jenis->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_jenis = $_POST['id_jenis'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $alasan = trim($_POST['alasan']);
    $action = $_POST['action']; // 'update_draft' atau 'kirim_sekarang'
    
    // Validasi
    if (empty($id_jenis) || empty($tanggal_mulai) || empty($tanggal_selesai) || empty($alasan)) {
        $error = "Semua field wajib diisi!";
    } elseif ($tanggal_selesai < $tanggal_mulai) {
        $error = "Tanggal selesai harus setelah tanggal mulai!";
    } else {
        // Hitung jumlah hari
        $start = new DateTime($tanggal_mulai);
        $end = new DateTime($tanggal_selesai);
        $jumlah_hari = $start->diff($end)->days + 1;
        
        // Cek maksimal hari cuti
        $sql_maks = "SELECT maks_hari FROM jenis_cuti WHERE id_jenis = ?";
        $stmt_maks = $pdo->prepare($sql_maks);
        $stmt_maks->execute([$id_jenis]);
        $maks_hari = $stmt_maks->fetchColumn();
        
        if ($jumlah_hari > $maks_hari) {
            $error = "Durasi cuti melebihi maksimal $maks_hari hari untuk jenis cuti ini!";
        } else {
            // Handle upload file baru
            $lampiran = $draft['lampiran'];
            $upload_error = '';
            
            if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
                // Validasi tipe file
                $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                $file_type = $_FILES['lampiran']['type'];
                $file_size = $_FILES['lampiran']['size'];
                $file_name = $_FILES['lampiran']['name'];
                
                if (!in_array($file_type, $allowed_types)) {
                    $upload_error = "Format file tidak didukung! Hanya PDF, JPG, dan PNG.";
                } elseif ($file_size > $max_size) {
                    $upload_error = "Ukuran file terlalu besar! Maksimal 5MB.";
                } else {
                    // Hapus file lama jika ada
                    if ($lampiran && file_exists('../uploads/' . $lampiran)) {
                        unlink('../uploads/' . $lampiran);
                    }
                    
                    // Generate nama file unik
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = 'lampiran_' . $id_user . '_' . time() . '.' . $file_ext;
                    $upload_path = '../uploads/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $upload_path)) {
                        $lampiran = $new_filename;
                    } else {
                        $upload_error = "Gagal mengunggah file!";
                    }
                }
                
                if (!empty($upload_error)) {
                    $error = $upload_error;
                }
            }
            
            // Jika ada error upload, hentikan proses
            if (empty($error)) {
                $status = ($action == 'kirim_sekarang') ? 'pending' : 'draft';
                
                // Update database
                $sql = "UPDATE pengajuan_cuti 
                        SET id_jenis = ?, tanggal_mulai = ?, tanggal_selesai = ?, 
                            alasan = ?, lampiran = ?, status = ?, dibuat_pada = NOW()
                        WHERE id_pengajuan = ? AND id_user = ?";
                
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$id_jenis, $tanggal_mulai, $tanggal_selesai, $alasan, $lampiran, $status, $id_pengajuan, $id_user])) {
                    if ($action == 'kirim_sekarang') {
                        $_SESSION['success'] = "Pengajuan cuti berhasil dikirim! Menunggu persetujuan admin.";
                    } else {
                        $_SESSION['success'] = "Draft cuti berhasil diperbarui!";
                    }
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Gagal memperbarui draft!";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Draft Cuti - Pegawai</title>
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
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            border: none;
            border-radius: 8px;
        }
        .user-info {
            font-size: 0.9rem;
        }
        .form-label { 
            font-weight: 500;
            margin-bottom: 5px;
        }
        .alert {
            border-radius: 8px;
        }
        .file-info {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .required::after {
            content: " *";
            color: #dc3545;
        }
        .current-file {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            border-left: 4px solid #17a2b8;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <span class="navbar-text me-3 text-white">
                    Halo <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong> – Pegawai
                </span>
                <a class="nav-link text-white" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Draft Cuti</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <!-- Jenis Cuti -->
                            <div class="mb-4">
                                <label class="form-label required">Jenis Cuti</label>
                                <select class="form-select" name="id_jenis" required>
                                    <option value="">Pilih Jenis Cuti</option>
                                    <?php foreach ($jenis_cuti as $jenis): ?>
                                        <option value="<?php echo $jenis['id_jenis']; ?>" 
                                            <?php echo ($draft['id_jenis'] == $jenis['id_jenis']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($jenis['nama_jenis']); ?> 
                                            (Maks: <?php echo $jenis['maks_hari']; ?> hari)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Tanggal Cuti -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label required">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="tanggal_mulai" 
                                           value="<?php echo $draft['tanggal_mulai']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="tanggal_selesai" 
                                           value="<?php echo $draft['tanggal_selesai']; ?>" required>
                                </div>
                            </div>

                            <!-- Lampiran/Surat -->
                            <div class="mb-4">
                                <label class="form-label">Lampiran Surat</label>
                                
                                <?php if ($draft['lampiran']): ?>
                                <div class="current-file mb-3">
                                    <strong>File saat ini:</strong>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span><?php echo $draft['lampiran']; ?></span>
                                        <a href="../uploads/<?php echo $draft['lampiran']; ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <input type="file" class="form-control" name="lampiran" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="file-info">
                                    Unggah file baru untuk mengganti lampiran
                                    <br>
                                    Format: PDF, JPG, PNG (Maksimal: 5MB)
                                </div>
                            </div>

                            <!-- Alasan Cuti -->
                            <div class="mb-4">
                                <label class="form-label required">Alasan Cuti</label>
                                <textarea class="form-control" name="alasan" rows="4" required><?php echo htmlspecialchars($draft['alasan']); ?></textarea>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                                <a href="dashboard.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" name="action" value="update_draft" class="btn btn-warning me-md-2">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                                <button type="submit" name="action" value="kirim_sekarang" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Kirim Sekarang
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</body>
</html>