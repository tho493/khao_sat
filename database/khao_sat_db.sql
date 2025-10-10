-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 25, 2025
-- Server version: 8.0.30
-- PHP Version: 8.1.12
-- Create by: tho493

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Tạo database
CREATE DATABASE IF NOT EXISTS `khao_sat_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `khao_sat_db`;

-- --------------------------------------------------------

-- Bảng tài khoản quản trị
CREATE TABLE IF NOT EXISTS `taikhoan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tendangnhap` VARCHAR(50) NOT NULL UNIQUE,
  `matkhau` VARCHAR(255) NOT NULL,
  `hoten` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100),
  `sodienthoai` VARCHAR(20),
  -- `quyen` ENUM('admin', 'manager', 'viewer') DEFAULT 'viewer', -- Dành cho phân quyền
  `trangthai` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `last_login` DATETIME,
  PRIMARY KEY (`id`),
  KEY `idx_tendangnhap` (`tendangnhap`),
  KEY `idx_trangthai` (`trangthai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng phân quyền chi tiết.
-- CREATE TABLE IF NOT EXISTS `phanquyen` (
--   `id` INT(11) NOT NULL AUTO_INCREMENT,
--   `taikhoan_id` INT(11) NOT NULL,
--   `chucnang` VARCHAR(50) NOT NULL,
--   `quyen` ENUM('view', 'create', 'edit', 'delete', 'full') DEFAULT 'view',
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `unique_taikhoan_chucnang` (`taikhoan_id`, `chucnang`),
--   CONSTRAINT `fk_phanquyen_taikhoan` FOREIGN KEY (`taikhoan_id`) 
--     REFERENCES `taikhoan` (`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng năm học
CREATE TABLE IF NOT EXISTS `namhoc` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `namhoc` VARCHAR(10) NOT NULL UNIQUE,
  `trangthai` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_namhoc` (`namhoc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chương trình đào tạo
CREATE TABLE IF NOT EXISTS `ctdt` (
  `mactdt` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `tenctdt` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `ctdt`
--

INSERT INTO `ctdt` (`mactdt`, `tenctdt`) VALUES
('7140234', 'Sư phạm Tiếng Trung Quốc'),
('7140246', 'Sư phạm công nghệ'),
('7220201', 'Ngôn ngữ Anh'),
('7220204', 'Ngôn ngữ Trung Quốc'),
('7310630', 'Việt Nam học (Hướng dẫn Du lịch)'),
('7340101', 'Quản trị kinh doanh'),
('7340301', 'Kế toán'),
('7380101', 'Luật'),
('7480201', 'Công nghệ thông tin'),
('7510201', 'Kỹ thuật điều khiển và tự động hóa'),
('7510205', 'Công nghệ kỹ thuật ô tô'),
('7510301', 'Công nghệ kỹ thuật điện, điện tử'),
('7510302', 'Công nghệ kỹ thuật điện tử, viễn thông'),
('7520114', 'Kỹ thuật cơ điện tử'),
('7520216', 'Công nghệ kỹ thuật cơ khí'),
('7540101', 'Công nghệ thực phẩm'),
('7540106', 'Đảm bảo chất lượng và an toàn thực phẩm'),
('7540204', 'Công nghệ dệt, may'),
('7810103', 'Quản trị dịch vụ du lịch và lữ hành');

-- --------------------------------------------------------
-- CÁC BẢNG QUẢN LÝ KHẢO SÁT
-- --------------------------------------------------------

-- Bảng mẫu khảo sát
CREATE TABLE IF NOT EXISTS `mau_khaosat` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_mau` VARCHAR(255) NOT NULL,
  `mota` TEXT,
  `version` INT DEFAULT 1,
  `trangthai` ENUM('draft', 'active', 'inactive') DEFAULT 'draft',
  `nguoi_tao_id` INT(11),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trangthai` (`trangthai`),
  KEY `idx_nguoi_tao` (`nguoi_tao_id`),
  CONSTRAINT `fk_mau_nguoitao` FOREIGN KEY (`nguoi_tao_id`) 
    REFERENCES `taikhoan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng câu hỏi khảo sát
CREATE TABLE IF NOT EXISTS `cauhoi_khaosat` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `mau_khaosat_id` INT(11) NOT NULL,
  `noidung_cauhoi` TEXT NOT NULL,
  `loai_cauhoi` ENUM('single_choice', 'multiple_choice', 'text', 'likert', 'rating', 'date', 'number', 'select_ctdt') DEFAULT 'single_choice',
  `batbuoc` TINYINT(1) DEFAULT 1,
  `is_personal_info` TINYINT(1) DEFAULT 0, -- để xác định câu hỏi dành cho thông tin người khảo sát
  `thutu` INT DEFAULT 0,
  `page` INT UNSIGNED DEFAULT(1),
  `cau_dieukien_id` INT(11), -- Câu hỏi phụ thuộc
  `dieukien_hienthi` JSON, -- Điều kiện hiển thị câu hỏi
  `trangthai` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mau_khaosat` (`mau_khaosat_id`),
  KEY `idx_cau_dieukien` (`cau_dieukien_id`),
  CONSTRAINT `fk_cauhoi_mau` FOREIGN KEY (`mau_khaosat_id`) 
    REFERENCES `mau_khaosat` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng phương án trả lời
CREATE TABLE IF NOT EXISTS `phuongan_traloi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cauhoi_id` INT(11) NOT NULL,
  `noidung` VARCHAR(500) NOT NULL,
  `giatri` VARCHAR(50),
  `thutu` INT DEFAULT 0,
  `cho_nhap_khac` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cauhoi` (`cauhoi_id`),
  CONSTRAINT `fk_phuongan_cauhoi` FOREIGN KEY (`cauhoi_id`) 
    REFERENCES `cauhoi_khaosat` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng đợt khảo sát
CREATE TABLE IF NOT EXISTS `dot_khaosat` (
  `id` VARCHAR(50) NOT NULL,
  `ten_dot` VARCHAR(255) NOT NULL,
  `mau_khaosat_id` INT(11) NOT NULL,
  `namhoc_id` INT(11),
  `tungay` DATETIME NOT NULL,
  `denngay` DATETIME NOT NULL,
  `trangthai` ENUM('draft', 'active', 'closed') DEFAULT 'draft',
  `mota` TEXT,
  `image_url` VARCHAR(255),
  `nguoi_tao_id` INT(11),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mau_khaosat` (`mau_khaosat_id`),
  KEY `idx_namhoc` (`namhoc_id`),
  KEY `idx_trangthai_ngay` (`trangthai`, `tungay`, `denngay`),
  CONSTRAINT `fk_dot_mau` FOREIGN KEY (`mau_khaosat_id`) 
    REFERENCES `mau_khaosat` (`id`),
  CONSTRAINT `fk_dot_namhoc` FOREIGN KEY (`namhoc_id`) 
    REFERENCES `namhoc` (`id`),
  CONSTRAINT `fk_dot_nguoitao` FOREIGN KEY (`nguoi_tao_id`) 
    REFERENCES `taikhoan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng phiếu khảo sát
CREATE TABLE IF NOT EXISTS `phieu_khaosat` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `dot_khaosat_id` VARCHAR(50) NOT NULL,
  -- `ma_nguoi_traloi` VARCHAR(50), -- Mã SV, mã NV, mã DN...
  -- `metadata` JSON, -- Thông tin người trả lời (họ tên, đơn vị, email...)
  `trangthai` ENUM('draft', 'completed') DEFAULT 'draft',
  `thoigian_batdau` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `thoigian_hoanthanh` DATETIME,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dot_khaosat` (`dot_khaosat_id`),
  -- KEY `idx_ma_nguoi_traloi` (`ma_nguoi_traloi`),
  KEY `idx_trangthai` (`trangthai`),
  KEY `idx_thoigian` (`thoigian_hoanthanh`),
  CONSTRAINT `fk_phieu_dot` FOREIGN KEY (`dot_khaosat_id`) 
    REFERENCES `dot_khaosat` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết phiếu khảo sát
CREATE TABLE IF NOT EXISTS `phieu_khaosat_chitiet` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `phieu_khaosat_id` INT(11) NOT NULL,
  `cauhoi_id` INT(11) NOT NULL,
  `phuongan_id` INT(11),
  `giatri_text` TEXT,
  `giatri_number` DECIMAL(10,2),
  `giatri_date` DATE,
  `giatri_json` JSON, -- Cho multiple choice
  `thoigian` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_phieu` (`phieu_khaosat_id`),
  KEY `idx_cauhoi` (`cauhoi_id`),
  KEY `idx_phuongan` (`phuongan_id`),
  CONSTRAINT `fk_chitiet_phieu` FOREIGN KEY (`phieu_khaosat_id`) 
    REFERENCES `phieu_khaosat` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chitiet_cauhoi` FOREIGN KEY (`cauhoi_id`) 
    REFERENCES `cauhoi_khaosat` (`id`),
  CONSTRAINT `fk_chitiet_phuongan` FOREIGN KEY (`phuongan_id`) 
    REFERENCES `phuongan_traloi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Bảng lịch sử thay đổi
CREATE TABLE IF NOT EXISTS `lichsu_thaydoi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `bang_thaydoi` VARCHAR(50) NOT NULL,
  `id_banghi` INT(11),
  `nguoi_thuchien_id` INT(11),
  `hanhdong` ENUM('create', 'update', 'delete', 'publish', 'close') NOT NULL,
  `noidung_cu` JSON,
  `noidung_moi` JSON,
  `ghi_chu` TEXT,
  `thoigian` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bang_id` (`bang_thaydoi`, `id_banghi`),
  KEY `idx_nguoi_thuchien` (`nguoi_thuchien_id`),
  KEY `idx_thoigian` (`thoigian`),
  CONSTRAINT `fk_lichsu_nguoi` FOREIGN KEY (`nguoi_thuchien_id`) 
    REFERENCES `taikhoan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng thông báo
CREATE TABLE IF NOT EXISTS `thongbao` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tieude` VARCHAR(255) NOT NULL,
  `noidung` TEXT,
  `loai_thongbao` ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
  -- `doi_tuong` VARCHAR(50), 
  `dot_khaosat_id` VARCHAR(50),
  `trangthai` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `ngay_hethan` DATETIME,
  PRIMARY KEY (`id`),
  KEY `idx_trangthai_ngay` (`trangthai`, `created_at`, `ngay_hethan`),
  KEY `idx_dot_khaosat` (`dot_khaosat_id`),
  CONSTRAINT `fk_thongbao_dot` FOREIGN KEY (`dot_khaosat_id`) 
    REFERENCES `dot_khaosat` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng template email
CREATE TABLE IF NOT EXISTS `template_email` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_template` VARCHAR(50) NOT NULL UNIQUE,
  `ten_template` VARCHAR(255) NOT NULL,
  `tieude` VARCHAR(255) NOT NULL,
  `noidung` TEXT NOT NULL,
  `bien_template` JSON, -- Các biến có thể sử dụng
  `trangthai` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_ma_template` (`ma_template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `chatbot_qa` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- CÁC VIEW ĐỂ TRUY VẤN NHANH
-- --------------------------------------------------------

-- View thống kê đợt khảo sát
CREATE OR REPLACE VIEW v_thongke_dot_khaosat AS
SELECT 
  dk.id,
  dk.ten_dot,
  dk.tungay,
  dk.denngay,
  dk.trangthai,
  mk.ten_mau,
  COUNT(DISTINCT pk.id) AS tong_phieu,
  COUNT(DISTINCT CASE WHEN pk.trangthai = 'completed' THEN pk.id END) AS phieu_hoanthanh,
  ROUND(COUNT(DISTINCT CASE WHEN pk.trangthai = 'completed' THEN pk.id END) * 100.0 / 
        NULLIF(COUNT(DISTINCT pk.id), 0), 2) AS ty_le_hoanthanh
FROM dot_khaosat dk
LEFT JOIN mau_khaosat mk ON dk.mau_khaosat_id = mk.id
LEFT JOIN phieu_khaosat pk ON dk.id = pk.dot_khaosat_id
GROUP BY dk.id;

-- View danh sách khảo sát đang hoạt động
CREATE OR REPLACE VIEW v_khaosat_hoatdong AS
SELECT 
  dk.*,
  mk.ten_mau
FROM dot_khaosat dk
JOIN mau_khaosat mk ON dk.mau_khaosat_id = mk.id
WHERE dk.trangthai = 'active'
  AND CURDATE() BETWEEN dk.tungay AND dk.denngay;

-- --------------------------------------------------------
-- CÁC STORED PROCEDURES
-- --------------------------------------------------------

DELIMITER //

-- Procedure tạo mẫu khảo sát mới
CREATE PROCEDURE sp_TaoMauKhaoSat(
  IN p_ten_mau VARCHAR(255),
  IN p_mota TEXT,
  IN p_nguoi_tao_id INT
)
BEGIN
  DECLARE v_mau_id INT;
  
  -- Tạo mẫu khảo sát
  INSERT INTO mau_khaosat (ten_mau, mota, nguoi_tao_id)
  VALUES (p_ten_mau, p_mota, p_nguoi_tao_id);
  
  SET v_mau_id = LAST_INSERT_ID();
  
  -- Ghi log
  INSERT INTO lichsu_thaydoi (bang_thaydoi, id_banghi, nguoi_thuchien_id, hanhdong)
  VALUES ('mau_khaosat', v_mau_id, p_nguoi_tao_id, 'create');
  
  SELECT v_mau_id AS mau_khaosat_id;
END//

DELIMITER ;

DELIMITER //

-- Procedure sao chép mẫu khảo sát
CREATE PROCEDURE sp_SaoChepMauKhaoSat(
  IN p_mau_goc_id INT,
  IN p_ten_mau_moi VARCHAR(255),
  IN p_nguoi_tao_id INT
)
BEGIN
  DECLARE v_mau_moi_id INT;
  
  -- Tạo mẫu mới
  INSERT INTO mau_khaosat (ten_mau, mota, nguoi_tao_id)
  SELECT p_ten_mau_moi, CONCAT('Sao chép từ: ', ten_mau), p_nguoi_tao_id
  FROM mau_khaosat WHERE id = p_mau_goc_id;
  
  SET v_mau_moi_id = LAST_INSERT_ID();
  
  -- Sao chép câu hỏi và phương án
  INSERT INTO cauhoi_khaosat (mau_khaosat_id, noidung_cauhoi, loai_cauhoi, batbuoc, thutu, page)
  SELECT v_mau_moi_id, noidung_cauhoi, loai_cauhoi, batbuoc, thutu, page
  FROM cauhoi_khaosat c WHERE mau_khaosat_id = p_mau_goc_id;
  
  -- Ghi log
  INSERT INTO lichsu_thaydoi (bang_thaydoi, id_banghi, nguoi_thuchien_id, hanhdong, ghi_chu)
  VALUES ('mau_khaosat', v_mau_moi_id, p_nguoi_tao_id, 'create', 
          CONCAT('Sao chép từ mẫu ID: ', p_mau_goc_id));
  
  SELECT v_mau_moi_id AS mau_khaosat_id;
END//

DELIMITER ;

DELIMITER //

-- Procedure tạo đợt khảo sát
CREATE PROCEDURE sp_TaoDotKhaoSat(
  IN p_ten_dot VARCHAR(255),
  IN p_mau_khaosat_id INT,
  IN p_namhoc_id INT,
  IN p_tungay DATE,
  IN p_denngay DATE,
  IN p_nguoi_tao_id INT
)
BEGIN
  DECLARE v_dot_id INT;
  
  -- Kiểm tra ngày hợp lệ
  IF p_tungay > p_denngay THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ngày bắt đầu phải trước ngày kết thúc';
  END IF;
  
  -- Tạo đợt khảo sát
  INSERT INTO dot_khaosat (ten_dot, mau_khaosat_id, namhoc_id, tungay, denngay, nguoi_tao_id)
  VALUES (p_ten_dot, p_mau_khaosat_id, p_namhoc_id, p_tungay, p_denngay, p_nguoi_tao_id);
  
  SET v_dot_id = LAST_INSERT_ID();
  
  -- Ghi log
  INSERT INTO lichsu_thaydoi (bang_thaydoi, id_banghi, nguoi_thuchien_id, hanhdong)
  VALUES ('dot_khaosat', v_dot_id, p_nguoi_tao_id, 'create');
  
  SELECT v_dot_id AS dot_khaosat_id;
END//

DELIMITER ;

DELIMITER //

-- Procedure xuất kết quả khảo sát (MySQL syntax)
CREATE PROCEDURE sp_XuatKetQuaKhaoSat(
  IN p_dot_khaosat_id INT,
  IN p_cauhoi_id INT
)
BEGIN
  IF p_cauhoi_id IS NULL OR p_cauhoi_id = 0 THEN
    -- Xuất tất cả câu hỏi
    SELECT 
      ch.id AS cauhoi_id,
      ch.noidung_cauhoi,
      ch.loai_cauhoi,
      pt.noidung AS phuongan,
      COUNT(pc.id) AS so_luong,
      ROUND(COUNT(pc.id) * 100.0 / 
            (SELECT COUNT(DISTINCT pc2.phieu_khaosat_id) 
             FROM phieu_khaosat_chitiet pc2 
             INNER JOIN phieu_khaosat pk2 ON pc2.phieu_khaosat_id = pk2.id
             WHERE pc2.cauhoi_id = ch.id AND pk2.dot_khaosat_id = p_dot_khaosat_id), 2) AS ty_le
    FROM cauhoi_khaosat ch
    LEFT JOIN phuongan_traloi pt ON ch.id = pt.cauhoi_id
    LEFT JOIN phieu_khaosat_chitiet pc ON pt.id = pc.phuongan_id
    LEFT JOIN phieu_khaosat pk ON pc.phieu_khaosat_id = pk.id
    WHERE pk.dot_khaosat_id = p_dot_khaosat_id OR pk.dot_khaosat_id IS NULL
    GROUP BY ch.id, pt.id
    ORDER BY ch.thutu, pt.thutu;
  ELSE
    SELECT 
      pt.noidung AS phuongan,
      COUNT(pc.id) AS so_luong,
      ROUND(COUNT(pc.id) * 100.0 / 
            (SELECT COUNT(DISTINCT pc2.phieu_khaosat_id) 
             FROM phieu_khaosat_chitiet pc2
             INNER JOIN phieu_khaosat pk2 ON pc2.phieu_khaosat_id = pk2.id
             WHERE pc2.cauhoi_id = p_cauhoi_id AND pk2.dot_khaosat_id = p_dot_khaosat_id), 2) AS ty_le
    FROM phuongan_traloi pt
    LEFT JOIN phieu_khaosat_chitiet pc ON pt.id = pc.phuongan_id
    LEFT JOIN phieu_khaosat pk ON pc.phieu_khaosat_id = pk.id
    WHERE pt.cauhoi_id = p_cauhoi_id 
      AND (pk.dot_khaosat_id = p_dot_khaosat_id OR pk.dot_khaosat_id IS NULL)
    GROUP BY pt.id
    ORDER BY pt.thutu;
  END IF;
END//

DELIMITER ;

-- --------------------------------------------------------
-- CÁC TRIGGER
-- --------------------------------------------------------

-- Trigger kiểm tra đợt khảo sát còn hoạt động
DELIMITER $$
CREATE TRIGGER `trg_KiemTraDotKhaoSat` 
BEFORE INSERT ON `phieu_khaosat`
FOR EACH ROW
BEGIN
    DECLARE v_trangthai VARCHAR(20);
    DECLARE v_denngay DATE;

    -- Chỉ thực thi trigger nếu biến @disable_triggers không được set
    IF @disable_triggers IS NULL THEN
        SELECT trangthai, denngay INTO v_trangthai, v_denngay
        FROM dot_khaosat
        WHERE id = NEW.dot_khaosat_id;

        IF v_trangthai != 'active' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Đợt khảo sát không hoạt động';
        END IF;

        IF CURDATE() > v_denngay THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Đợt khảo sát đã kết thúc';
        END IF;
    END IF;
END$$
DELIMITER ;

-- Trigger tự động cập nhật trạng thái đợt khảo sát
DELIMITER //
CREATE TRIGGER trg_CapNhatTrangThaiDot
BEFORE UPDATE ON dot_khaosat
FOR EACH ROW
BEGIN
  IF NEW.trangthai = 'draft' AND NOW() >= NEW.tungay THEN
    SET NEW.trangthai = 'active';
  END IF;
  
  IF NEW.trangthai = 'active' AND NOW() > NEW.denngay THEN
    SET NEW.trangthai = 'closed';
  END IF;
END//

DELIMITER ;

-- -- Trigger ghi log thay đổi mẫu khảo sát
-- DELIMITER //
-- CREATE TRIGGER trg_LogThayDoiMauKhaoSat
-- AFTER UPDATE ON mau_khaosat
-- FOR EACH ROW
-- BEGIN
--   INSERT INTO lichsu_thaydoi (bang_thaydoi, id_banghi, hanhdong, noidung_cu, noidung_moi)
--   VALUES ('mau_khaosat', NEW.id, 'update',
--           JSON_OBJECT('ten_mau', OLD.ten_mau, 'trangthai', OLD.trangthai),
--           JSON_OBJECT('ten_mau', NEW.ten_mau, 'trangthai', NEW.trangthai));
-- END//

-- DELIMITER ;

-- --------------------------------------------------------
-- CÁC FUNCTION HỖ TRỢ
-- --------------------------------------------------------

DELIMITER //

-- Function tính tỷ lệ hoàn thành
CREATE FUNCTION fn_TinhTyLeHoanThanh(p_dot_khaosat_id INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_tong INT;
  DECLARE v_hoanthanh INT;
  
  SELECT 
    COUNT(*),
    COUNT(CASE WHEN trangthai = 'completed' THEN 1 END)
  INTO v_tong, v_hoanthanh
  FROM phieu_khaosat
  WHERE dot_khaosat_id = p_dot_khaosat_id;
  
  IF v_tong = 0 THEN
    RETURN 0;
  END IF;
  
  RETURN ROUND(v_hoanthanh * 100.0 / v_tong, 2);
END//

DELIMITER ;

DELIMITER //

-- Function kiểm tra quyền truy cập
CREATE FUNCTION fn_KiemTraQuyen(
  p_taikhoan_id INT,
  p_chucnang VARCHAR(50),
  p_quyen VARCHAR(10)
)
RETURNS BOOLEAN
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_quyen_taikhoan VARCHAR(10);
  DECLARE v_quyen_phanquyen VARCHAR(10);
  
  -- Kiểm tra quyền admin
  SELECT quyen INTO v_quyen_taikhoan
  FROM taikhoan
  WHERE id = p_taikhoan_id;
  
  IF v_quyen_taikhoan = 'admin' THEN
    RETURN TRUE;
  END IF;
  
  -- Kiểm tra phân quyền chi tiết
  SELECT quyen INTO v_quyen_phanquyen
  FROM phanquyen
  WHERE taikhoan_id = p_taikhoan_id AND chucnang = p_chucnang;
  
  IF v_quyen_phanquyen = 'full' OR v_quyen_phanquyen = p_quyen THEN
    RETURN TRUE;
  END IF;
  
  RETURN FALSE;
END//

DELIMITER ;

-- --------------------------------------------------------
-- DỮ LIỆU MẪU
-- --------------------------------------------------------

-- Thêm tài khoản admin mặc định
INSERT INTO `taikhoan` (`tendangnhap`, `matkhau`, `hoten`, `email`) VALUES
('tho493', '2584fcf4f93b79886a1bba3c47dc5cac', 'Administrator', 'tho493@admin.com');

-- Thêm năm học
INSERT INTO `namhoc` (`namhoc`) VALUES
('2023-2024'),
('2024-2025'),
('2025-2026');


-- Import dữ liệu cho bảng `chatbot_qa`
INSERT INTO `chatbot_qa` (`id`, `keywords`, `question`, `answer`, `is_enabled`, `created_at`, `updated_at`) VALUES
(1, 'bảo mật,an toàn,dữ liệu,cá nhân', 'Thông tin của tôi có được bảo mật không?', 'Chào bạn, chúng tôi cam kết mọi thông tin cá nhân và câu trả lời của bạn đều được <strong>bảo mật tuyệt đối</strong> và chỉ được sử dụng cho mục đích thống kê, nghiên cứu tổng hợp.', 1, '2025-08-24 22:43:59', '2025-09-05 14:42:25'),
(2, 'hạn,cuối,hạn chót,kết thúc', 'Khi nào là hạn cuối của khảo sát này?', 'Chào bạn, mỗi đợt khảo sát sẽ có thời gian kết thúc riêng. Bạn có thể xem hạn cuối được ghi rõ ở đầu trang khảo sát nhé!', 1, '2025-08-24 22:43:59', NULL),
(3, 'bắt buộc,thiếu,bỏ qua', 'Tôi có phải trả lời tất cả câu hỏi không?', 'Chào bạn, bạn nên trả lời tất cả các câu hỏi để cung cấp thông tin đầy đủ nhất. Tuy nhiên, chỉ những câu hỏi có dấu sao màu đỏ (*) là bắt buộc phải trả lời.', 1, '2025-08-24 22:43:59', NULL),
(4, 'làm gì,mục đích', 'Khảo sát này dùng để làm gì?', 'Cảm ơn bạn đã quan tâm! Khảo sát này nhằm thu thập ý kiến đóng góp để nhà trường có thể cải thiện và nâng cao chất lượng đào tạo và các dịch vụ hỗ trợ.', 1, '2025-08-24 22:43:59', NULL),
(5, 'lỗi,gửi,submit,không được', 'Tôi bị lỗi không gửi được khảo sát?', 'Chào bạn, nếu bạn không gửi được khảo sát, vui lòng thử các bước sau: <br>1. Kiểm tra lại kết nối mạng. <br>2. Đảm bảo đã trả lời tất cả các câu hỏi bắt buộc (*). <br>3. Xác thực reCAPTCHA \"Tôi không phải người máy\". <br>4. Thử tải lại trang và làm lại. Dữ liệu của bạn đã được tự động lưu.', 1, '2025-08-24 22:43:59', NULL),
(6, 'cảm ơn,ok,chào', 'Chào bot', 'Chào bạn, tôi là trợ lý ảo của hệ thống khảo sát. Tôi có thể giúp gì cho bạn?', 1, '2025-08-24 22:43:59', NULL);


-- --------------------------------------------------------
-- CÁC INDEX BỔ SUNG CHO HIỆU NĂNG
-- --------------------------------------------------------

-- Index cho tìm kiếm và thống kê
-- CREATE INDEX idx_phieu_metadata_khoa ON phieu_khaosat((CAST(JSON_EXTRACT(metadata, '$.khoa') AS CHAR(50))));
CREATE INDEX idx_lichsu_thoigian_bang ON lichsu_thaydoi(thoigian, bang_thaydoi);
CREATE INDEX idx_chitiet_composite ON phieu_khaosat_chitiet(phieu_khaosat_id, cauhoi_id, phuongan_id);

-- --------------------------------------------------------
-- EVENT TỰ ĐỘNG
-- --------------------------------------------------------

DELIMITER //

-- Event tự động cập nhật trạng thái đợt khảo sát
CREATE EVENT IF NOT EXISTS evt_CapNhatTrangThaiDot
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE
DO
BEGIN
  -- Kích hoạt các đợt đến ngày
  UPDATE dot_khaosat 
  SET trangthai = 'active'
  WHERE trangthai = 'draft' 
    AND CURDATE() >= tungay;
  
  -- Đóng các đợt quá hạn
  UPDATE dot_khaosat 
  SET trangthai = 'closed'
  WHERE trangthai = 'active' 
    AND CURDATE() > denngay;
END//

DELIMITER ;

-- --------------------------------------------------------
-- GRANT QUYỀN CHO USER
-- --------------------------------------------------------

-- Tạo user cho ứng dụng (thay đổi password khi deploy)
-- CREATE USER 'khaosat_app'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON khaosat_db_optimized.* TO 'khaosat_app'@'localhost';
-- GRANT CREATE TEMPORARY TABLES ON khaosat_db_optimized.* TO 'khaosat_app'@'localhost';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;