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

if ($_POST) {
    $nip = $_POST['nip'];
    $password_input = $_POST['password'];
    
    try {
        // Query untuk mendapatkan user data
        $sql = "SELECT u.*, d.nama_departemen 
                FROM users u 
                LEFT JOIN departemen d ON u.id_departemen = d.id_departemen 
                WHERE u.nip = :nip";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nip', $nip);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Cek password (plain text untuk sample)
            if ($user['password'] === $password_input) {
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['nip'] = $user['nip'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['no_telp'] = $user['no_telp'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['departemen'] = $user['nama_departemen'];
                $_SESSION['id_departemen'] = $user['id_departemen'];
                
                if ($user['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: pegawai/dashboard.php");
                }
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "NIP tidak ditemukan!";
        }
    } catch(PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistem Cuti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center">Login Sistem Cuti</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>NIP</label>
                                <input type="text" name="nip" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <!-- <div class="text-center mt-3">
                            <p>Pegawai baru? <a href="register.php">Daftar akun di sini</a></p>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>