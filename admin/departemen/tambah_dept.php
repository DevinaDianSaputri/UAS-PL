<?php
session_start();
include '../../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_departemen = trim($_POST['nama_departemen']);

    if (empty($nama_departemen)) {
        $error = "Nama departemen tidak boleh kosong!";
    } else {
        // Cek nama duplikat
        $sql_check = "SELECT COUNT(*) FROM departemen WHERE nama_departemen = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$nama_departemen]);
        
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Nama departemen sudah ada!";
        } else {
            $sql = "INSERT INTO departemen (nama_departemen) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$nama_departemen])) {
                $_SESSION['success'] = "Departemen berhasil ditambahkan!";
                header("Location: departemen.php");
                exit();
            } else {
                $error = "Gagal menambahkan departemen!";
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
    <title>Tambah Departemen - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link" href="departemen.php">Departemen</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tambah Departemen Baru</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nama Departemen</label>
                                <input type="text" class="form-control" name="nama_departemen" required 
                                       placeholder="Contoh: IT, HRD, Keuangan">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Tambah</button>
                                <a href="departemen.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>