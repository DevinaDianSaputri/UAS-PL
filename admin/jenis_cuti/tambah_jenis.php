<?php
session_start();
include '../../config.php';

// Cek login dan role admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Proses tambah jenis cuti
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_jenis = $_POST['nama_jenis'];
    $deskripsi_cuti = $_POST['deskripsi_cuti'];
    $maks_hari = $_POST['maks_hari'];

    $sql = "INSERT INTO jenis_cuti (nama_jenis, deskripsi_cuti, maks_hari) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$nama_jenis, $deskripsi_cuti, $maks_hari])) {
        $_SESSION['success'] = "Jenis cuti berhasil ditambahkan!";
        header("Location: jenis_cuti.php");
        exit();
    } else {
        $error = "Gagal menambahkan jenis cuti!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jenis Cuti - Admin</title>
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
                        <h5 class="mb-0">Tambah Jenis Cuti Baru</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nama Jenis Cuti</label>
                                <input type="text" class="form-control" name="nama_jenis" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Maksimal Hari</label>
                                <input type="number" class="form-control" name="maks_hari" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="deskripsi_cuti" rows="3"></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Tambah</button>
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