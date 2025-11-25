<?php
include '../config.php';
if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$pengajuan_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Pastikan pengajuan milik user yang login dan status draft
    $sql = "SELECT * FROM pengajuan_cuti WHERE id = :id AND user_id = :user_id AND status = 'draft'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $pengajuan_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['error'] = "Pengajuan tidak ditemukan atau tidak dapat diajukan!";
        header("Location: dashboard.php");
        exit();
    }
    
    // Update status menjadi pending
    $sql_update = "UPDATE pengajuan_cuti SET status = 'pending' WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':id', $pengajuan_id);
    
    if ($stmt_update->execute()) {
        $_SESSION['success'] = "Pengajuan cuti berhasil dikirim! Menunggu persetujuan admin.";
    } else {
        $_SESSION['error'] = "Gagal mengirim pengajuan!";
    }
    
} catch(PDOException $e) {
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
}

header("Location: dashboard.php");
exit();
?>