<?php
session_start();
include '../../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: departemen.php");
    exit();
}

$id_departemen = $_GET['id'];

// Cek apakah departemen memiliki karyawan
$sql_check = "SELECT COUNT(*) as total FROM users WHERE id_departemen = ?";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$id_departemen]);
$result = $stmt_check->fetch(PDO::FETCH_ASSOC);

if ($result['total'] > 0) {
    // Jika ada karyawan, set id_departemen menjadi NULL
    $sql_update = "UPDATE users SET id_departemen = NULL WHERE id_departemen = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$id_departemen]);
    
    // Kemudian hapus departemen
    $sql = "DELETE FROM departemen WHERE id_departemen = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id_departemen])) {
        $_SESSION['success'] = "Departemen berhasil dihapus! Karyawan yang terkait telah diubah menjadi tanpa departemen.";
    } else {
        $_SESSION['error'] = "Gagal menghapus departemen!";
    }
} else {
    // Jika tidak ada karyawan, langsung hapus
    $sql = "DELETE FROM departemen WHERE id_departemen = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id_departemen])) {
        $_SESSION['success'] = "Departemen berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus departemen!";
    }
}

header("Location: departemen.php");
exit();
?>