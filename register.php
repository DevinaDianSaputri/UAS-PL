<?php
include 'config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: pegawai/dashboard.php");
    }
    exit();
}

try {
    // Get departemen untuk dropdown
    $sql_departemen = "SELECT * FROM departemen ORDER BY nama_departemen";
    $stmt_departemen = $pdo->prepare($sql_departemen);
    $stmt_departemen->execute();

    $error = '';
    $success = '';

    if ($_POST) {
        $nip = $_POST['nip'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $email = $_POST['email'];
        $no_telp = $_POST['no_telp'];
        $departemen_id = $_POST['departemen_id'];

        // Validasi
        if (empty($nip) || empty($password) || empty($nama_lengkap) || empty($email) || empty($no_telp)) {
            $error = "Semua field wajib diisi!";
        } elseif ($password !== $confirm_password) {
            $error = "Password dan konfirmasi password tidak cocok!";
        } elseif (strlen($password) < 6) {
            $error = "Password minimal 6 karakter!";
        } else {
            // Validasi NIP unik
            $sql_check_nip = "SELECT id FROM users WHERE nip = :nip";
            $stmt_check_nip = $pdo->prepare($sql_check_nip);
            $stmt_check_nip->bindParam(':nip', $nip);
            $stmt_check_nip->execute();

            if ($stmt_check_nip->rowCount() > 0) {
                $error = "NIP sudah digunakan!";
            } else {
                // Validasi email unik
                $sql_check_email = "SELECT id FROM users WHERE email = :email";
                $stmt_check_email = $pdo->prepare($sql_check_email);
                $stmt_check_email->bindParam(':email', $email);
                $stmt_check_email->execute();

                if ($stmt_check_email->rowCount() > 0) {
                    $error = "Email sudah digunakan!";
                } else {
                    // Insert user baru
                    $sql = "INSERT INTO users (nip, password, nama_lengkap, email, no_telp, role, departemen_id) 
                            VALUES (:nip, :password, :nama_lengkap, :email, :no_telp, 'pegawai', :departemen_id)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':nip', $nip);
                    $stmt->bindParam(':password', $password);
                    $stmt->bindParam(':nama_lengkap', $nama_lengkap);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':no_telp', $no_telp);
                    $stmt->bindParam(':departemen_id', $departemen_id);
                    
                    if ($stmt->execute()) {
                        $success = "Pendaftaran berhasil! Silakan login dengan NIP dan password Anda.";
                    } else {
                        $error = "Gagal mendaftar! Silakan coba lagi.";
                    }
                }
            }
        }
    }
} catch(PDOException $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Akun Pegawai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center">Daftar Akun Pegawai</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">NIP</label>
                                        <input type="text" name="nip" class="form-control" value="<?php echo isset($_POST['nip']) ? htmlspecialchars($_POST['nip']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Departemen</label>
                                        <select name="departemen_id" class="form-select" required>
                                            <option value="">Pilih Departemen</option>
                                            <?php while ($dept = $stmt_departemen->fetch(PDO::FETCH_ASSOC)): ?>
                                                <option value="<?php echo $dept['id']; ?>" <?php echo (isset($_POST['departemen_id']) && $_POST['departemen_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dept['nama_departemen']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" name="no_telp" class="form-control" value="<?php echo isset($_POST['no_telp']) ? htmlspecialchars($_POST['no_telp']) : ''; ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Konfirmasi Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Daftar</button>
                                <a href="login.php" class="btn btn-outline-secondary">Kembali ke Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>