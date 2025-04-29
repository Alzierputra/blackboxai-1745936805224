-- Core Database Structure for Futsal Field Rental System

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100),
    telepon VARCHAR(15),
    alamat TEXT,
    email VARCHAR(100),
    password VARCHAR(100),
    kode_verifikasi VARCHAR(6),
    status_verifikasi BOOLEAN DEFAULT FALSE,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Futsal Fields Table
CREATE TABLE lapangan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100),
    deskripsi TEXT,
    harga_per_jam DECIMAL(10,2),
    foto VARCHAR(255),
    status ENUM('tersedia', 'maintenance') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE pemesanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    lapangan_id INT,
    jenis_pemesanan ENUM('reguler', 'turnamen'),
    waktu_mulai DATETIME,
    waktu_selesai DATETIME,
    total_harga DECIMAL(10,2),
    diskon DECIMAL(10,2) DEFAULT 0,
    metode_pembayaran ENUM('transfer', 'qris', 'cod'),
    status_pembayaran ENUM('menunggu', 'dibayar', 'batal') DEFAULT 'menunggu',
    status_pemesanan ENUM('aktif', 'selesai', 'dibatalkan', 'kadaluarsa') DEFAULT 'aktif',
    batas_pembayaran DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (lapangan_id) REFERENCES lapangan(id)
);

-- Payments Table
CREATE TABLE pembayaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pemesanan_id INT,
    jumlah DECIMAL(10,2),
    metode_pembayaran ENUM('transfer', 'qris', 'cod'),
    bukti_pembayaran VARCHAR(255),
    status ENUM('menunggu', 'dikonfirmasi', 'ditolak') DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id)
);

-- Bank Information Table
CREATE TABLE informasi_bank (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_bank VARCHAR(100),
    nomor_rekening VARCHAR(50),
    nama_pemilik VARCHAR(100)
);

-- Reviews and Ratings Table
CREATE TABLE ulasan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pemesanan_id INT,
    user_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Admin Replies to Reviews Table
CREATE TABLE balasan_ulasan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ulasan_id INT,
    admin_id INT,
    balasan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ulasan_id) REFERENCES ulasan(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Cancellations Table
CREATE TABLE pembatalan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pemesanan_id INT,
    alasan_pembatalan TEXT,
    waktu_pembatalan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_pengembalian ENUM('tidak_ada_pengembalian', 'diproses', 'selesai') DEFAULT 'tidak_ada_pengembalian',
    jumlah_pengembalian DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id)
);

-- Insert default bank information
INSERT INTO informasi_bank (nama_bank, nomor_rekening, nama_pemilik) 
VALUES ('SeaBank', '901245634730', 'Muhammad Alzier Putra Purnama');

-- Insert default admin account
INSERT INTO users (nama, email, password, role, status_verifikasi) 
VALUES ('Admin', 'admin@admin.com', 'admin123', 'admin', TRUE);

-- Create uploads directory
CREATE TABLE IF NOT EXISTS `uploads` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `directory` VARCHAR(255)
);

-- Insert default uploads directory
INSERT INTO `uploads` (`directory`) VALUES ('uploads/lapangan');
