CREATE DATABASE IF NOT EXISTS campustrace_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campustrace_db;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  student_id VARCHAR(50) NOT NULL UNIQUE,
  college VARCHAR(150) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('lost','found') NOT NULL,
  title VARCHAR(160) NOT NULL,
  category VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  location VARCHAR(180) NOT NULL,
  event_date DATE NOT NULL,
  color VARCHAR(60) DEFAULT NULL,
  identifying_details VARCHAR(500) DEFAULT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  status ENUM('open','matched','returned','archived') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_items_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_items_browse (type,status,category,event_date),
  INDEX idx_items_search (title,location,category)
) ENGINE=InnoDB;

CREATE TABLE claims (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id INT UNSIGNED NOT NULL,
  claimant_id INT UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  proof_details TEXT DEFAULT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_claim_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
  CONSTRAINT fk_claim_user FOREIGN KEY (claimant_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_claim (item_id,claimant_id),
  INDEX idx_claim_status (status)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type VARCHAR(40) NOT NULL,
  message VARCHAR(255) NOT NULL,
  link VARCHAR(255) DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_notifications_user (user_id,is_read,created_at)
) ENGINE=InnoDB;

CREATE TABLE reports (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id INT UNSIGNED NOT NULL,
  reporter_id INT UNSIGNED NOT NULL,
  reason VARCHAR(120) NOT NULL,
  details TEXT DEFAULT NULL,
  status ENUM('open','reviewed','dismissed') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reports_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
  CONSTRAINT fk_reports_user FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Demo admin password is: Admin@12345
-- Change it immediately in production.
INSERT INTO users(name,email,password_hash,student_id,college,role) VALUES
('Campus Administrator','admin@campustrace.local','$2y$12$c6s/gVfu2g1fFXLDnlPmgO6whMO3YvnbvBMb8ke3w8E0jHq2y6qzm','ADMIN-001','CampusTrace Demo College','admin');

INSERT INTO items(user_id,type,title,category,description,location,event_date,color,identifying_details,status) VALUES
(1,'found','Black wireless earbuds','Electronics','Found a compact pair of wireless earbuds inside a charging case.','Central Library – 2nd Floor',CURRENT_DATE - INTERVAL 2 DAY,'Black','Small scratch near the hinge.', 'open'),
(1,'lost','Blue college hoodie','Clothing','Navy-blue hoodie with a small college crest on the chest.','Student Activity Center',CURRENT_DATE - INTERVAL 4 DAY,'Navy','Size M; initials stitched inside.', 'open');
