<?php
include '../config.php';
if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

try {
    // Get jenis cuti untuk dropdown
    $sql_jenis = "SELECT * FROM jenis_cuti ORDER BY nama_jenis";
    $stmt_jenis = $pdo->prepare($sql_jenis);
    $stmt_jenis->execute();
    
    $error = '';
    
    if ($_POST) {
        $jenis_cuti_id = $_POST['jenis_cuti_id'];
        $tanggal_mulai = $_POST['tanggal_mulai'];
        $tanggal_selesai = $_POST['tanggal_selesai'];
        $alasan = $_POST['alasan'];
        
        // Validasi
        if (empty($jenis_cuti_id) || empty($tanggal_mulai) || empty($tanggal_selesai) || empty($alasan)) {
            $error = "Semua field wajib diisi!";
        } elseif ($tanggal_mulai > $tanggal_selesai) {
            $error = "Tanggal mulai tidak boleh lebih besar dari tanggal selesai!";
        } else {
            // Hitung jumlah hari
            $start = new DateTime($tanggal_mulai);
            $end = new DateTime($tanggal_selesai);
            $jumlah_hari = $start->diff($end)->days + 1;
            
            // Validasi maksimal hari
            $sql_jenis = "SELECT maksimal_hari FROM jenis_cuti WHERE id = :id";
            $stmt_jenis_validasi = $pdo->prepare($sql_jenis);
            $stmt_jenis_validasi->bindParam(':id', $jenis_cuti_id);
            $stmt_jenis_validasi->execute();
            $jenis = $stmt_jenis_validasi->fetch(PDO::FETCH_ASSOC);
            
            if ($jumlah_hari > $jenis['maksimal_hari']) {
                $error = "Jumlah hari cuti melebihi batas maksimal (" . $jenis['maksimal_hari'] . " hari)!";
            } else {
                // Insert sebagai draft
                $sql = "INSERT INTO pengajuan_cuti (user_id, jenis_cuti_id, tanggal_mulai, tanggal_selesai, alasan, status) 
                        VALUES (:user_id, :jenis_cuti_id, :tanggal_mulai, :tanggal_selesai, :alasan, 'draft')";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':jenis_cuti_id', $jenis_cuti_id);
                $stmt->bindParam(':tanggal_mulai', $tanggal_mulai);
                $stmt->bindParam(':tanggal_selesai', $tanggal_selesai);
                $stmt->bindParam(':alasan', $alasan);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Pengajuan cuti berhasil dibuat sebagai draft!";
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Gagal membuat pengajuan cuti!";
                }
            }
        }
    }
    
} catch(PDOException $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pengajuan Cuti - Sistem Cuti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center">Buat Pengajuan Cuti</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Jenis Cuti</label>
                                <select name="jenis_cuti_id" class="form-select" required>
                                    <option value="">Pilih Jenis Cuti</option>
                                    <?php while ($jenis = $stmt_jenis->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $jenis['id']; ?>" <?php echo isset($_POST['jenis_cuti_id']) && $_POST['jenis_cuti_id'] == $jenis['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($jenis['nama_jenis']); ?> 
                                            (Maksimal: <?php echo $jenis['maksimal_hari']; ?> hari)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Mulai</label>
                                        <input type="date" name="tanggal_mulai" class="form-control" 
                                               value="<?php echo isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Selesai</label>
                                        <input type="date" name="tanggal_selesai" class="form-control" 
                                               value="<?php echo isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Alasan Cuti</label>
                                <textarea name="alasan" class="form-control" rows="4" placeholder="Tuliskan alasan pengajuan cuti..." required><?php echo isset($_POST['alasan']) ? htmlspecialchars($_POST['alasan']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan sebagai Draft</button>
                                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>