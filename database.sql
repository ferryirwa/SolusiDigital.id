-- ============================================
-- DATABASE: Website Jasa Programmer
-- ============================================

CREATE DATABASE IF NOT EXISTS db_jasa_programmer
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_jasa_programmer;

-- ============================================
-- TABEL: admin
-- ============================================
CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) DEFAULT NULL,
  role ENUM('super','admin') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Password default: admin123 (hash bcrypt)
INSERT INTO admin (username, password, nama, email, role) VALUES
('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HcGX5BTsTzWQx4bD1t5Z8zWQx4bD1t', 'Administrator', 'admin@website.com', 'super');

-- ============================================
-- TABEL: layanan
-- ============================================
CREATE TABLE layanan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  deskripsi_singkat TEXT,
  deskripsi_lengkap LONGTEXT,
  harga_mulai INT DEFAULT 0,
  icon VARCHAR(50) DEFAULT 'bi-code-slash',
  gambar VARCHAR(255) DEFAULT NULL,
  urutan INT DEFAULT 0,
  status ENUM('aktif','nonaktif') DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO layanan (nama, slug, deskripsi_singkat, deskripsi_lengkap, harga_mulai, icon, urutan) VALUES
('Pembuatan Website', 'pembuatan-website', 'Website company profile, toko online, dan custom sesuai kebutuhan bisnis Anda.', 'Kami menyediakan jasa pembuatan website profesional mulai dari landing page, company profile, e-commerce, hingga sistem informasi custom. Menggunakan teknologi modern seperti PHP, Laravel, dan React.', 2500000, 'bi-globe', 1),
('Aplikasi Android', 'aplikasi-android', 'Aplikasi Android native & hybrid untuk kebutuhan bisnis Anda.', 'Pengembangan aplikasi Android menggunakan Kotlin, Java, atau Flutter. Cocok untuk startup, UMKM, hingga enterprise.', 5000000, 'bi-phone', 2),
('Hosting & Domain', 'hosting-domain', 'Layanan hosting cepat, aman, dan support 24/7.', 'Paket hosting mulai dari personal hingga bisnis. Include domain, SSL gratis, dan backup otomatis.', 300000, 'bi-server', 3),
('Maintenance Website', 'maintenance-website', 'Perawatan & update website berkala agar selalu optimal.', 'Layanan maintenance bulanan meliputi update konten, backup, monitoring keamanan, dan optimasi kecepatan.', 500000, 'bi-tools', 4),
('Desain UI/UX', 'desain-uiux', 'Desain antarmuka modern & user-friendly untuk web/app.', 'Jasa desain UI/UX menggunakan Figma. Termasuk wireframe, prototype, dan design system.', 1500000, 'bi-palette', 5),
('SEO Optimization', 'seo-optimization', 'Tingkatkan peringkat website di Google.', 'Optimasi SEO on-page & off-page untuk meningkatkan visibilitas website Anda di mesin pencari.', 1000000, 'bi-graph-up-arrow', 6);

-- ============================================
-- TABEL: pesanan
-- ============================================
CREATE TABLE pesanan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_pesanan VARCHAR(30) NOT NULL UNIQUE,
  nama_klien VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  whatsapp VARCHAR(20) NOT NULL,
  layanan_id INT DEFAULT NULL,
  budget INT DEFAULT 0,
  deskripsi TEXT,
  file_lampiran VARCHAR(255) DEFAULT NULL,
  status ENUM('baru','proses','selesai','batal') DEFAULT 'baru',
  catatan_admin TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (layanan_id) REFERENCES layanan(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABEL: portfolio
-- ============================================
CREATE TABLE portfolio (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  slug VARCHAR(170) NOT NULL UNIQUE,
  kategori VARCHAR(50) DEFAULT 'web',
  klien VARCHAR(100) DEFAULT NULL,
  deskripsi TEXT,
  gambar VARCHAR(255) DEFAULT NULL,
  link_demo VARCHAR(255) DEFAULT NULL,
  teknologi VARCHAR(200) DEFAULT NULL,
  tanggal_selesai DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABEL: testimoni
-- ============================================
CREATE TABLE testimoni (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  jabatan VARCHAR(100) DEFAULT NULL,
  perusahaan VARCHAR(100) DEFAULT NULL,
  isi TEXT NOT NULL,
  foto VARCHAR(255) DEFAULT NULL,
  rating TINYINT DEFAULT 5,
  status ENUM('tampil','sembunyi') DEFAULT 'tampil',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO testimoni (nama, jabatan, perusahaan, isi, rating) VALUES
('Budi Santoso', 'Owner', 'TokoBaju.id', 'Pelayanan cepat, hasil website rapi dan profesional. Recommended!', 5),
('Siti Aminah', 'CEO', 'Kopi Nusantara', 'Aplikasi Android yang dibuat sangat membantu bisnis kami. Terima kasih!', 5),
('Andi Wijaya', 'Founder', 'StartupXYZ', 'Timnya responsif dan paham kebutuhan klien. Puas dengan hasilnya.', 5);

-- ============================================
-- TABEL: artikel
-- ============================================
CREATE TABLE artikel (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  konten LONGTEXT,
  gambar VARCHAR(255) DEFAULT NULL,
  penulis VARCHAR(100) DEFAULT 'Admin',
  status ENUM('publish','draft') DEFAULT 'draft',
  views INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABEL: pesan_kontak
-- ============================================
CREATE TABLE pesan_kontak (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  subjek VARCHAR(150) DEFAULT NULL,
  pesan TEXT NOT NULL,
  status ENUM('belum','dibaca') DEFAULT 'belum',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABEL: pengaturan
-- ============================================
CREATE TABLE pengaturan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_situs VARCHAR(100) DEFAULT 'JasaProgrammer.id',
  logo VARCHAR(255) DEFAULT NULL,
  deskripsi TEXT,
  alamat TEXT,
  email VARCHAR(100) DEFAULT 'info@jasaprogrammer.id',
  whatsapp VARCHAR(20) DEFAULT '6281234567890',
  facebook VARCHAR(255) DEFAULT NULL,
  instagram VARCHAR(255) DEFAULT NULL,
  meta_keyword TEXT,
  meta_description TEXT
) ENGINE=InnoDB;

INSERT INTO pengaturan (nama_situs, deskripsi, alamat, email, whatsapp, meta_keyword, meta_description) VALUES
('JasaProgrammer.id', 'Jasa pembuatan website, aplikasi Android, hosting, dan layanan IT profesional.', 'Jl. Teknologi No. 123, Jakarta', 'info@jasaprogrammer.id', '6281234567890', 'jasa website, jasa android, jasa hosting, jasa programmer', 'Kami menyediakan jasa pembuatan website, aplikasi Android, hosting, dan layanan IT profesional dengan harga terjangkau.');