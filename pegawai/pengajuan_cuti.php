<?php
include '../config.php';
if (!isLoggedIn() || !isPegawai()) {
    header("Location: ../login.php");
    exit();
}

try {
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
            $stmt_jenis_validasi = $pdo->prepare($sql_jenis);
            $stmt_jenis_validasi->bindParam(':id', $jenis_cuti_id);
            $stmt_jenis_validasi->execute();
            $jenis = $stmt_jenis_validasi->fetch(PDO::FETCH_ASSOC);
            
            if ($jumlah_hari > $jenis['maksimal_hari']) {
                $error = "Jumlah hari cuti melebihi batas maksimal (" . $jenis['maksimal_hari'] . " hari)!";
            } else {
                // Insert sebagai draft
                $sql = "INSERT INTO pengajuan_cuti (user_id, jenis_cuti_id, tanggal_mulai, tanggal_selesai, alasan, status) 
                        VALUES (:user_id, :jenis_cuti_id, :tanggal_mulai, :tanggal_selesai, :alasan, 'draft')";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':jenis_cuti_id', $jenis_cuti_id);
                $stmt->bindParam(':tanggal_mulai', $tanggal_mulai);
                $stmt->bindParam(':tanggal_selesai', $tanggal_selesai);
                $stmt->bindParam(':alasan', $alasan);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Pengajuan cuti berhasil dibuat sebagai draft!";
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Gagal membuat pengajuan cuti!";
                }
            }
        }
    }
    
} catch(PDOException $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}
?>
