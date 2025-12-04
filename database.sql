-- Buat database
CREATE DATABASE IF NOT EXISTS sistem_cuti;
USE sistem_cuti;
-- Tabel departemen
CREATE TABLE departemen (
    id_departemen INT PRIMARY KEY AUTO_INCREMENT,
    nama_departemen VARCHAR(100) NOT NULL
);

-- Tabel users
CREATE TABLE users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    no_telp VARCHAR(15) NOT NULL,
    role ENUM('admin', 'pegawai') NOT NULL,
    id_departemen INT NULL,
    FOREIGN KEY (id_departemen) REFERENCES departemen(id_departemen)
);

-- Tabel Jenis Cuti
CREATE TABLE jenis_cuti (
    id_jenis INT PRIMARY KEY AUTO_INCREMENT,
    nama_jenis VARCHAR(100) NOT NULL,
    deskripsi_cuti TEXT,
    maks_hari INT NOT NULL DEFAULT 0
);

-- Tabel pengajuan_cuti
CREATE TABLE pengajuan_cuti (
    id_pengajuan INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    id_jenis INT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    alasan TEXT NOT NULL,
    lampiran VARCHAR(255),
    status ENUM('draft', 'pending', 'disetujui', 'ditolak') DEFAULT 'draft',
    alasan_admin TEXT NULL,
    tanggal_disetujui TIMESTAMP NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user),
    FOREIGN KEY (id_jenis) REFERENCES jenis_cuti(id_jenis)
);

INSERT INTO departemen (nama_departemen) VALUES 
('HRD'),
('IT'),
('Keuangan'),
('Marketing'),
('Produksi'),
('Logistik');
INSERT INTO users (nip, password, nama_lengkap, email, no_telp, role, id_departemen) VALUES 
('001001', 'admin001', 'Ahmad Wijaya', 'ahmad.wijaya@perusahaan.com', '081234567890', 'admin', 1),
('001002', 'admin002', 'Sari Dewi', 'sari.dewi@perusahaan.com', '081234567891', 'admin', 1),
('001003', 'admin003', 'Rizki Pratama', 'rizki.pratama@perusahaan.com', '081234567892', 'admin', 1),
('002001', 'user2001', 'Budi Santoso', 'budi.santoso@perusahaan.com', '081234567893', 'pegawai', 2),
('002002', 'user2002', 'Siti Rahayu', 'siti.rahayu@perusahaan.com', '081234567894', 'pegawai', 3),
('002003', 'user2003', 'Dian Permata', 'dian.permata@perusahaan.com', '081234567895', 'pegawai', 4);
INSERT INTO jenis_cuti (nama_jenis, deskripsi_cuti, maks_hari) VALUES 
('Cuti Tahunan', 'Cuti tahunan yang diberikan kepada karyawan', 12),
('Cuti Sakit', 'Cuti karena sakit dengan surat dokter', 30),
('Cuti Melahirkan', 'Cuti untuk karyawan yang melahirkan', 90),
('Cuti Penting', 'Cuti untuk keperluan penting keluarga', 3),
('Cuti Bersalin', 'Cuti untuk istri yang melahirkan', 2),
('Cuti Alasan Penting', 'Cuti karena alasan penting lainnya', 14);

