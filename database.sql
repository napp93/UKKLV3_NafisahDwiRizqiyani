-- Etalase Digital Stella - Skema Basis Data
-- Import lewat phpMyAdmin (tab Import) atau: mysql -u root < database.sql

CREATE DATABASE IF NOT EXISTS etalase_stella CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE etalase_stella;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin','siswa') NOT NULL DEFAULT 'siswa',
    bio TEXT NULL,
    foto VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_project VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    gambar VARCHAR(255) NULL,
    link_project VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Akun uji (password semua akun: password123)
INSERT IGNORE INTO users (nama, email, password, role) VALUES
('Super Admin Stella', 'superadmin@stella.sch.id', '$2y$10$rYJWjuxdmDDQOyk/SktyGen.Ldy5y54B7AAahQQJMm/ngqAhQD7xy', 'super_admin'),
('Admin Stella',       'admin@stella.sch.id',      '$2y$10$rYJWjuxdmDDQOyk/SktyGen.Ldy5y54B7AAahQQJMm/ngqAhQD7xy', 'admin'),
('Siswa Stella',       'siswa@stella.sch.id',      '$2y$10$rYJWjuxdmDDQOyk/SktyGen.Ldy5y54B7AAahQQJMm/ngqAhQD7xy', 'siswa');
