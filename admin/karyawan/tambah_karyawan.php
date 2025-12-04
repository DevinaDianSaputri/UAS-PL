<?php
session_start();
include '../../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../../login.php");
    exit();
}

// Ambil data departemen untuk dropdown
$sql_dept = "SELECT * FROM departemen ORDER BY nama_departemen";
$stmt_dept = $pdo->query($sql_dept);
$departemen = $stmt_dept->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nip = trim($_POST['nip']);
    $email = trim($_POST['email']);
    $password = password_hash('password123', PASSWORD_DEFAULT); // Password default
    $no_telp = trim($_POST['no_telp']);
    $role = 'pegawai';
    $id_departemen = $_POST['id_departemen'] ?: NULL;

    // Validasi
    if (empty($nama_lengkap) || empty($nip) || empty($email) || empty($no_telp)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Cek NIP duplikat
        $sql_check = "SELECT COUNT(*) FROM users WHERE nip = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$nip]);
        
        if ($stmt_check->fetchColumn() > 0) {
            $error = "NIP sudah digunakan!";
        } else {
            // Cek email duplikat
            $sql_check = "SELECT COUNT(*) FROM users WHERE email = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$email]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $error = "Email sudah digunakan!";
            } else {
                $sql = "INSERT INTO users (nama_lengkap, nip, email, password, no_telp, role, id_departemen) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$nama_lengkap, $nip, $email, $password, $no_telp, $role, $id_departemen])) {
                    $_SESSION['success'] = "Karyawan berhasil ditambahkan! Password default: password123";
                    header("Location: karyawan.php");
                    exit();
                } else {
                    $error = "Gagal menambahkan karyawan!";
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
    <title>Tambah Karyawan - Admin</title>
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
                        <h5 class="mb-0">Tambah Karyawan Baru</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_lengkap" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NIP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nip" required>
                                    <small class="text-muted">Nomor Induk Pegawai (unik)</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="no_telp" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Departemen</label>
                                    <select class="form-select" name="id_departemen">
                                        <option value="">Pilih Departemen</option>
                                        <?php foreach ($departemen as $dept): ?>
                                            <option value="<?php echo $dept['id_departemen']; ?>">
                                                <?php echo htmlspecialchars($dept['nama_departemen']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control" value="Pegawai" readonly>
                                    <small class="text-muted">Default role untuk karyawan baru</small>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <strong>Informasi:</strong> Password default untuk karyawan baru adalah <code>password123</code>. 
                                Karyawan dapat mengganti password setelah login pertama kali.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Tambah Karyawan</button>
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