-- ============================================
-- PERBAIKAN SKEMA & DATA — Website Jasa Programmer
-- Jalankan sekali kalau database sudah terlanjur dibuat
-- dengan versi lama (mis. import database.sql sebelum perbaikan)
-- ============================================

USE db_jasa_programmer;

-- --------------------------------------------
-- 1) Tambah kolom target_selesai pada tabel pesanan
--    (form pemesanan sudah mengirim field ini, tapi sebelumnya tidak disimpan)
-- --------------------------------------------
ALTER TABLE pesanan
  ADD COLUMN IF NOT EXISTS target_selesai DATE DEFAULT NULL AFTER file_lampiran;

-- --------------------------------------------
-- 2) Perbaiki layanan yang ikut tercemar bug bind_param
--    (icon tersimpan '0' dan status tersimpan kosong saat layanan diedit)
-- --------------------------------------------
UPDATE layanan SET icon = 'bi-server', status = 'aktif'
  WHERE id = 3 AND (icon = '0' OR status = '');

-- Sisa baris lain yang masih tercemar (kalau ada)
UPDATE layanan SET status = 'aktif' WHERE status = '';
UPDATE layanan SET icon = 'bi-code-slash' WHERE icon = '0';

-- --------------------------------------------
-- 3) Perbaiki nomor WhatsApp website yang kehilangan kode negara
--    (ganti angka di bawah dengan nomor WhatsApp Anda)
-- --------------------------------------------
UPDATE pengaturan SET whatsapp = '6287799130382'
  WHERE id = 1 AND whatsapp NOT LIKE '62%';
