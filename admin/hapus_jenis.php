<?php
session_start();
include '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    header("Location: jenis_cuti.php");
    exit();
}

$id_jenis = $_GET['id'];

// Cek apakah jenis cuti digunakan
$sql_check = "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE id_jenis = ?";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$id_jenis]);
$result = $stmt_check->fetch(PDO::FETCH_ASSOC);

if ($result['total'] > 0) {
    $_SESSION['error'] = "Jenis cuti tidak dapat dihapus karena sudah digunakan!";
} else {
    // Hapus jenis cuti
    $sql = "DELETE FROM jenis_cuti WHERE id_jenis = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id_jenis])) {
        $_SESSION['success'] = "Jenis cuti berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus jenis cuti!";
    }
}

header("Location: jenis_cuti.php");
exit();
?>