-- Buat database
CREATE DATABASE IF NOT EXISTS sistem_cuti;
USE sistem_cuti;

-- Tabel departemen
CREATE TABLE departemen (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_departemen VARCHAR(100) NOT NULL
);

-- Tabel users (pegawai dan admin) dengan NIP, email, no_telp
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    no_telp VARCHAR(15) NOT NULL,
    role ENUM('admin', 'pegawai') NOT NULL,
    departemen_id INT NULL,
    FOREIGN KEY (departemen_id) REFERENCES departemen(id)
);


-- Tabel Jenis Cuti
CREATE TABLE jenis_cuti (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_jenis VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    maksimal_hari INT NOT NULL DEFAULT 0
);


-- Tabel pengajuan_cuti
CREATE TABLE pengajuan_cuti (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jenis_cuti_id INT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    alasan TEXT NOT NULL,
    lampiran VARCHAR(255),
    status ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    admin_id INT NULL,
    alasan_admin TEXT NULL,
    tanggal_disetujui TIMESTAMP NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (jenis_cuti_id) REFERENCES jenis_cuti(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

