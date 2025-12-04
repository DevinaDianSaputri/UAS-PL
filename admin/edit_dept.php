<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: departemen.php");
    exit();
}

$id_departemen = $_GET['id'];

// Ambil data departemen
$sql = "SELECT * FROM departemen WHERE id_departemen = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_departemen]);
$departemen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$departemen) {
    header("Location: departemen.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_departemen = trim($_POST['nama_departemen']);

    if (empty($nama_departemen)) {
        $error = "Nama departemen tidak boleh kosong!";
    } else {
        // Cek nama duplikat (kecuali untuk dirinya sendiri)
        $sql_check = "SELECT COUNT(*) FROM departemen WHERE nama_departemen = ? AND id_departemen != ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$nama_departemen, $id_departemen]);
        
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Nama departemen sudah digunakan!";
        } else {
            $sql = "UPDATE departemen SET nama_departemen = ? WHERE id_departemen = ?";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$nama_departemen, $id_departemen])) {
                $_SESSION['success'] = "Departemen berhasil diperbarui!";
                header("Location: departemen.php");
                exit();
            } else {
                $error = "Gagal memperbarui departemen!";
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
    <title>Edit Departemen - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
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
                        <h5 class="mb-0">Edit Departemen</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nama Departemen</label>
                                <input type="text" class="form-control" name="nama_departemen" 
                                       value="<?php echo htmlspecialchars($departemen['nama_departemen']); ?>" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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