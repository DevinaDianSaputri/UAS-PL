<?php
include '../config.php';
if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Query untuk history pengajuan user (selain draft)
    $sql = "
        SELECT pc.*, jc.nama_jenis, d.nama_departemen,
               u_admin.nama_lengkap as admin_nama
        FROM pengajuan_cuti pc 
        JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
        JOIN users u ON pc.user_id = u.id
        JOIN departemen d ON u.departemen_id = d.id
        LEFT JOIN users u_admin ON pc.admin_id = u_admin.id
        WHERE pc.user_id = :user_id AND pc.status != 'draft'
        ORDER BY pc.dibuat_pada DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
