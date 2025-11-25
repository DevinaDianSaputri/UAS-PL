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
        $_SESSION['error'] = "Pengajuan tidak ditemukan atau tidak dapat dihapus!";
        header("Location: dashboard.php");
        exit();
    }
    
    // Hapus pengajuan
    $sql_delete = "DELETE FROM pengajuan_cuti WHERE id = :id";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->bindParam(':id', $pengajuan_id);
    
    if ($stmt_delete->execute()) {
        $_SESSION['success'] = "Pengajuan berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus pengajuan!";
    }
    
} catch(PDOException $e) {
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
}

header("Location: dashboard.php");
exit();
?>