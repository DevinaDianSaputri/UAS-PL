<?php
session_start();
include '../../config.php';

// Cek login dan role admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    header("Location: jenis_cuti.php");
    exit();
}

$id_jenis = $_GET['id'];

// Ambil data jenis cuti yang akan diedit
$sql = "SELECT * FROM jenis_cuti WHERE id_jenis = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_jenis]);
$jenis_cuti = $stmt->fetch(PDO::FETCH_ASSOC);

// Cek apakah data ditemukan
if (!$jenis_cuti) {
    header("Location: jenis_cuti.php");
    exit();
}

// Proses edit jenis cuti
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_jenis = $_POST['nama_jenis'];
    $deskripsi_cuti = $_POST['deskripsi_cuti'];
    $maks_hari = $_POST['maks_hari'];
    
    $sql = "UPDATE jenis_cuti SET nama_jenis = ?, deskripsi_cuti = ?, maks_hari = ? WHERE id_jenis = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nama_jenis, $deskripsi_cuti, $maks_hari, $id_jenis])) {
        $_SESSION['success'] = "Jenis cuti berhasil diperbarui!";
        header("Location: jenis_cuti.php");
        exit();
    } else {
        $error = "Gagal memperbarui jenis cuti!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jenis Cuti - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link" href="jenis_cuti.php">Jenis Cuti</a>
                <a class="nav-link" href="../departemen.php">Departemen</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Jenis Cuti</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nama Jenis Cuti</label>
                                <input type="text" class="form-control" name="nama_jenis" 
                                       value="<?php echo htmlspecialchars($jenis_cuti['nama_jenis']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Maksimal Hari</label>
                                <input type="number" class="form-control" name="maks_hari" 
                                       value="<?php echo $jenis_cuti['maks_hari']; ?>" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="deskripsi_cuti" rows="3"><?php echo htmlspecialchars($jenis_cuti['deskripsi_cuti'] ?? ''); ?></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <a href="jenis_cuti.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>