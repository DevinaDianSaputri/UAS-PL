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
                LEFT JOIN departemen d ON u.departemen_id = d.id 
                WHERE u.nip = :nip";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nip', $nip);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Cek password (plain text untuk sample)
            if ($user['password'] === $password_input) {
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
