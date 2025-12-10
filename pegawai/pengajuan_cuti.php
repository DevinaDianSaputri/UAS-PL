<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$error = '';
$success = '';

// Ambil jenis cuti
$sql_jenis = "SELECT * FROM jenis_cuti ORDER BY nama_jenis";
$stmt_jenis = $pdo->query($sql_jenis);
$jenis_cuti = $stmt_jenis->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_jenis = $_POST['id_jenis'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $alasan = trim($_POST['alasan']);
    $action = $_POST['action']; // 'draft' atau 'kirim_sekarang'
    
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
            // Handle upload file
            $lampiran = null;
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
                    // Buat folder uploads jika belum ada
                    $upload_dir = '../uploads/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Generate nama file unik
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = 'lampiran_' . $id_user . '_' . time() . '.' . $file_ext;
                    $upload_path = $upload_dir . $new_filename;
                    
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
                
                // Insert ke database
                $sql = "INSERT INTO pengajuan_cuti 
                        (id_user, id_jenis, tanggal_mulai, tanggal_selesai, alasan, lampiran, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$id_user, $id_jenis, $tanggal_mulai, $tanggal_selesai, $alasan, $lampiran, $status])) {
                    if ($action == 'kirim_sekarang') {
                        $_SESSION['success'] = "Pengajuan cuti berhasil dikirim! Menunggu persetujuan admin.";
                    } else {
                        $_SESSION['success'] = "Draft cuti berhasil disimpan!";
                    }
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Gagal menyimpan pengajuan!";
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
    <title>Ajukan Cuti - Pegawai</title>
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
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
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
                        <h5 class="mb-0">Ajukan Cuti Baru</h5>
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
                                            <?php echo isset($_POST['id_jenis']) && $_POST['id_jenis'] == $jenis['id_jenis'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($jenis['nama_jenis']); ?> 
                                            (Maks: <?php echo $jenis['maks_hari']; ?> hari)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="file-info">
                                    Pilih jenis cuti sesuai kebutuhan
                                </div>
                            </div>

                            <!-- Tanggal Cuti -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label required">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="tanggal_mulai" 
                                           value="<?php echo $_POST['tanggal_mulai'] ?? ''; ?>" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="file-info">
                                        Format: dd/mm/yyyy
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="tanggal_selesai" 
                                           value="<?php echo $_POST['tanggal_selesai'] ?? ''; ?>" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="file-info">
                                        Format: dd/mm/yyyy
                                    </div>
                                </div>
                            </div>

                            <!-- Lampiran/Surat -->
                            <div class="mb-4">
                                <label class="form-label">Lampiran Surat</label>
                                <input type="file" class="form-control" name="lampiran" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="file-info">
                                    Format: PDF, JPG, PNG (Maksimal: 5MB)
                                    <br>
                                    <small class="text-muted">Contoh: Surat keterangan dokter, undangan, dll.</small>
                                </div>
                            </div>

                            <!-- Alasan Cuti -->
                            <div class="mb-4">
                                <label class="form-label required">Alasan Cuti</label>
                                <textarea class="form-control" name="alasan" rows="4" 
                                          placeholder="Jelaskan alasan cuti secara detail..." 
                                          required><?php echo $_POST['alasan'] ?? ''; ?></textarea>
                                <div class="file-info">
                                    Jelaskan secara lengkap alasan pengajuan cuti
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                                <a href="dashboard.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" name="action" value="draft" class="btn btn-warning me-md-2">
                                    <i class="bi bi-save"></i> Simpan Draft
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