<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: karyawan.php");
    exit();
}

$id_user = $_GET['id'];

// Cek apakah user sedang login (tidak bisa hapus diri sendiri)
if ($id_user == $_SESSION['user_id']) {
    $_SESSION['error'] = "Tidak dapat menghapus akun yang sedang aktif!";
    header("Location: karyawan.php");
    exit();
}

// Cek apakah karyawan memiliki pengajuan cuti
$sql_check = "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE id_user = ?";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$id_user]);
$result = $stmt_check->fetch(PDO::FETCH_ASSOC);

if ($result['total'] > 0) {
    $_SESSION['error'] = "Karyawan tidak dapat dihapus karena memiliki riwayat pengajuan cuti!";
} else {
    $sql = "DELETE FROM users WHERE id_user = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id_user])) {
        $_SESSION['success'] = "Karyawan berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus karyawan!";
    }
}

header("Location: karyawan.php");
exit();
?>