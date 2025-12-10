<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['id_pengajuan']) || !isset($_POST['action'])) {
        $_SESSION['error'] = "Data tidak lengkap!";
        header("Location: dashboard.php");
        exit();
    }

    $id_pengajuan = $_POST['id_pengajuan'];
    $action = $_POST['action']; // 'approve' atau 'reject'
    $alasan_admin = trim($_POST['alasan_admin'] ?? '');

    // Validasi action
    if (!in_array($action, ['approve', 'reject'])) {
        $_SESSION['error'] = "Aksi tidak valid!";
        header("Location: dashboard.php");
        exit();
    }

    // Validasi alasan untuk reject
    if ($action == 'reject' && empty($alasan_admin)) {
        $_SESSION['error'] = "Harap berikan alasan penolakan!";
        header("Location: dashboard.php");
        exit();
    }

    // Update status berdasarkan action
    if ($action == 'approve') {
        $status = 'disetujui';
        $message = "Pengajuan cuti berhasil disetujui!";
        // Untuk approve, alasan boleh kosong
        if (empty($alasan_admin)) {
            $alasan_admin = "Pengajuan cuti telah disetujui.";
        }
    } else {
        $status = 'ditolak';
        $message = "Pengajuan cuti berhasil ditolak!";
    }

    // Update data pengajuan
    $sql = "UPDATE pengajuan_cuti 
            SET status = ?, 
                alasan_admin = ?,
                tanggal_disetujui = NOW()
            WHERE id_pengajuan = ? AND status = 'pending'";
    
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$status, $alasan_admin, $id_pengajuan])) {
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = "Gagal memproses pengajuan!";
    }

    header("Location: dashboard.php");
    exit();
} else {
    // Jika bukan POST, redirect ke dashboard
    header("Location: dashboard.php");
    exit();
}
?>