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
    // Get data pengajuan
    $sql = "SELECT pc.*, jc.nama_jenis, jc.maksimal_hari 
            FROM pengajuan_cuti pc 
            JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
            WHERE pc.id = :id AND pc.user_id = :user_id AND pc.status = 'draft'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $pengajuan_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['error'] = "Pengajuan tidak ditemukan!";
        header("Location: dashboard.php");
        exit();
    }
    
    $pengajuan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get jenis cuti untuk dropdown
    $sql_jenis = "SELECT * FROM jenis_cuti ORDER BY nama_jenis";
    $stmt_jenis = $pdo->prepare($sql_jenis);
    $stmt_jenis->execute();
    
    $error = '';
    
    if ($_POST) {
        $jenis_cuti_id = $_POST['jenis_cuti_id'];
        $tanggal_mulai = $_POST['tanggal_mulai'];
        $tanggal_selesai = $_POST['tanggal_selesai'];
        $alasan = $_POST['alasan'];
        
        // Validasi
        if (empty($jenis_cuti_id) || empty($tanggal_mulai) || empty($tanggal_selesai) || empty($alasan)) {
            $error = "Semua field wajib diisi!";
        } elseif ($tanggal_mulai > $tanggal_selesai) {
            $error = "Tanggal mulai tidak boleh lebih besar dari tanggal selesai!";
        } else {
            // Hitung jumlah hari
            $start = new DateTime($tanggal_mulai);
            $end = new DateTime($tanggal_selesai);
            $jumlah_hari = $start->diff($end)->days + 1;
            
            // Validasi maksimal hari
            $sql_jenis = "SELECT maksimal_hari FROM jenis_cuti WHERE id = :id";
            $stmt_jenis = $pdo->prepare($sql_jenis);
            $stmt_jenis->bindParam(':id', $jenis_cuti_id);
            $stmt_jenis->execute();
            $jenis = $stmt_jenis->fetch(PDO::FETCH_ASSOC);
            
            if ($jumlah_hari > $jenis['maksimal_hari']) {
                $error = "Jumlah hari cuti melebihi batas maksimal (" . $jenis['maksimal_hari'] . " hari)!";
            } else {
                // Update pengajuan
                $sql_update = "UPDATE pengajuan_cuti 
                              SET jenis_cuti_id = :jenis_cuti_id, 
                                  tanggal_mulai = :tanggal_mulai, 
                                  tanggal_selesai = :tanggal_selesai, 
                                  alasan = :alasan 
                              WHERE id = :id";
                
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':jenis_cuti_id', $jenis_cuti_id);
                $stmt_update->bindParam(':tanggal_mulai', $tanggal_mulai);
                $stmt_update->bindParam(':tanggal_selesai', $tanggal_selesai);
                $stmt_update->bindParam(':alasan', $alasan);
                $stmt_update->bindParam(':id', $pengajuan_id);
                
                if ($stmt_update->execute()) {
                    $_SESSION['success'] = "Pengajuan berhasil diperbarui!";
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Gagal memperbarui pengajuan!";
                }
            }
        }
    }
    
} catch(PDOException $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}
?>
