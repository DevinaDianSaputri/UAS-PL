<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: dashboard.php");
    exit();
}

$id_pengajuan = $_GET['id'];
$action = $_GET['action']; // 'approve' atau 'reject'

// Validasi action
if (!in_array($action, ['approve', 'reject'])) {
    $_SESSION['error'] = "Aksi tidak valid!";
    header("Location: dashboard.php");
    exit();
}

// Update status berdasarkan action
if ($action == 'approve') {
    $status = 'disetujui';
    $message = "Pengajuan cuti berhasil disetujui!";
} else {
    $status = 'ditolak';
    $message = "Pengajuan cuti berhasil ditolak!";
}

$sql = "UPDATE pengajuan_cuti 
        SET status = ?, 
            tanggal_disetujui = NOW() 
        WHERE id_pengajuan = ? AND status = 'pending'";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$status, $id_pengajuan])) {
    $_SESSION['success'] = $message;
} else {
    $_SESSION['error'] = "Gagal memproses pengajuan!";
}

header("Location: dashboard.php");
exit();
?>