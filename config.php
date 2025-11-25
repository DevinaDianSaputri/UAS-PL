<?php
$host = 'localhost';
$dbname = 'sistem_cuti';
$username = 'root';
$password = '';

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Mengatur mode error agar PDO melempar Exception pada kesalahan
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Menghentikan skrip jika koneksi gagal
    die("Koneksi gagal: " . $e->getMessage());
}

// Function untuk check login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isPegawai() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'pegawai';
}

// Memulai Sesi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>