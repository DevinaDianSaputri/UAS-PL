-- Buat database
CREATE DATABASE sistem_cuti;
USE sistem_cuti;

-- Tabel User
CREATE TABLE user (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(20) UNIQUE NOT NULL,
    nama_user VARCHAR(100) NOT NULL,
    jabatan VARCHAR(50) NOT NULL,
    divisi VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    no_telepon VARCHAR(15),
    role ENUM('pegawai', 'atasan') DEFAULT 'pegawai',
    jatah_cuti INT DEFAULT 12
);

-- Tabel Jenis Cuti
CREATE TABLE jenis_cuti (
    id_jenis INT PRIMARY KEY AUTO_INCREMENT,
    jenis_cuti VARCHAR(50) NOT NULL,
    deskripsi_cuti TEXT,
    maks_hari INT DEFAULT 12
);

-- Tabel Cuti
CREATE TABLE cuti (
    id_cuti INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    id_jenis INT NOT NULL,
    tanggal_pengajuan DATETIME DEFAULT CURRENT_TIMESTAMP,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    jumlah_hari INT NOT NULL,
    alasan TEXT NOT NULL,
    status ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    disetujui_oleh VARCHAR(100),
    catatan_approval TEXT,
    tanggal_approval DATETIME,
    FOREIGN KEY (id_user) REFERENCES user(id_user),
    FOREIGN KEY (id_jenis) REFERENCES jenis_cuti(id_jenis)
);