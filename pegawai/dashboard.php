<?php
include '../config.php';
if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['id_user'];

try {
    // Query untuk draft pengajuan user
    $sql_draft = "
        SELECT pc.*, jc.nama_jenis, d.nama_departemen
        FROM pengajuan_cuti pc 
        JOIN jenis_cuti jc ON pc.id_jenis = jc.id_jenis 
        JOIN users u ON pc.id_user = u.id
        JOIN departemen d ON u.id_departemen = d.id
        WHERE pc.id_user = :user_id AND pc.status = 'draft'
        ORDER BY pc.dibuat_pada DESC
    ";
    $stmt_draft = $pdo->prepare($sql_draft);
    $stmt_draft->bindValue(':user_id', $user_id);
    $stmt_draft->execute();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
