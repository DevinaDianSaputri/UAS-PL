<?php
include 'config.php';

if ($_POST) {
    $nip = $_POST['nip'];
    $password_input = $_POST['password'];
    
    try {
        // Query untuk mendapatkan user data
        $sql = "SELECT u.*, d.nama_departemen 
                FROM users u 
                LEFT JOIN departemen d ON u.departemen_id = d.id 
                WHERE u.nip = :nip";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nip', $nip);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Cek jika password sudah di-hash atau masih plain
            if (password_verify($password_input, $user['password']) || $user['password'] === $password_input) {
                // Jika password masih plain, hash dan update ke database
                if ($user['password'] === $password_input) {
                    $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET password = :password WHERE id = :id";
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->bindParam(':password', $hashed_password);
                    $update_stmt->bindParam(':id', $user['id']);
                    $update_stmt->execute();
                }
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nip'] = $user['nip'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['no_telp'] = $user['no_telp'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['departemen'] = $user['nama_departemen'];
                $_SESSION['departemen_id'] = $user['departemen_id'];
                
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>