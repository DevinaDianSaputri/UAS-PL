<?php
session_start();
include '../../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: karyawan.php");
    exit();
}

$id_user = $_GET['id'];

// Ambil data karyawan
$sql = "SELECT * FROM users WHERE id_user = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_user]);
$karyawan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$karyawan) {
    header("Location: karyawan.php");
    exit();
}

// Ambil data departemen
$sql_dept = "SELECT * FROM departemen ORDER BY nama_departemen";
$stmt_dept = $pdo->query($sql_dept);
$departemen = $stmt_dept->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nip = trim($_POST['nip']);
    $email = trim($_POST['email']);
    $no_telp = trim($_POST['no_telp']);
    $id_departemen = $_POST['id_departemen'] ?: NULL;
    $role = $_POST['role']; // Bisa edit role

    // Validasi
    if (empty($nama_lengkap) || empty($nip) || empty($email) || empty($no_telp)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek NIP duplikat (kecuali untuk dirinya sendiri)
        $sql_check = "SELECT COUNT(*) FROM users WHERE nip = ? AND id_user != ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$nip, $id_user]);
        
        if ($stmt_check->fetchColumn() > 0) {
            $error = "NIP sudah digunakan oleh karyawan lain!";
        } else {
            // Cek email duplikat
            $sql_check = "SELECT COUNT(*) FROM users WHERE email = ? AND id_user != ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$email, $id_user]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $error = "Email sudah digunakan oleh karyawan lain!";
            } else {
                $sql = "UPDATE users SET 
                        nama_lengkap = ?, 
                        nip = ?, 
                        email = ?, 
                        no_telp = ?, 
                        id_departemen = ?, 
                        role = ? 
                        WHERE id_user = ?";
                
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$nama_lengkap, $nip, $email, $no_telp, $id_departemen, $role, $id_user])) {
                    $_SESSION['success'] = "Data karyawan berhasil diperbarui!";
                    header("Location: karyawan.php");
                    exit();
                } else {
                    $error = "Gagal memperbarui data karyawan!";
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
    <title>Edit Karyawan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">Sistem Cuti</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link" href="karyawan.php">Karyawan</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Data Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_lengkap" 
                                           value="<?php echo htmlspecialchars($karyawan['nama_lengkap']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NIP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nip" 
                                           value="<?php echo htmlspecialchars($karyawan['nip']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($karyawan['email']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="no_telp" 
                                           value="<?php echo htmlspecialchars($karyawan['no_telp']); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Departemen</label>
                                    <select class="form-select" name="id_departemen">
                                        <option value="">Pilih Departemen</option>
                                        <?php foreach ($departemen as $dept): ?>
                                            <option value="<?php echo $dept['id_departemen']; ?>"
                                                <?php echo ($karyawan['id_departemen'] == $dept['id_departemen']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dept['nama_departemen']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" name="role" required>
                                        <option value="pegawai" <?php echo ($karyawan['role'] == 'pegawai') ? 'selected' : ''; ?>>Pegawai</option>
                                        <option value="admin" <?php echo ($karyawan['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <a href="karyawan.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>