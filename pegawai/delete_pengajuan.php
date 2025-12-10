<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id_pengajuan = $_GET['id'];
$id_user = $_SESSION['id_user'];

// Hapus draft
$sql = "DELETE FROM pengajuan_cuti 
        WHERE id_pengajuan = ? AND id_user = ? AND status = 'draft'";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$id_pengajuan, $id_user])) {
    $_SESSION['success'] = "Draft berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus draft!";
}

header("Location: dashboard.php");
exit();
?>