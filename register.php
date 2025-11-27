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

