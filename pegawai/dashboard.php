<?php
include '../config.php';
if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Query untuk draft pengajuan user
    $sql_draft = "
        SELECT pc.*, jc.nama_jenis, d.nama_departemen
        FROM pengajuan_cuti pc 
        JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
        JOIN users u ON pc.user_id = u.id
        JOIN departemen d ON u.departemen_id = d.id
        WHERE pc.user_id = :user_id AND pc.status = 'draft'
        ORDER BY pc.dibuat_pada DESC
    ";
    $stmt_draft = $pdo->prepare($sql_draft);
    $stmt_draft->bindValue(':user_id', $user_id);
    $stmt_draft->execute();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
