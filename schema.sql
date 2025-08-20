-- Create database (optional if already created)
CREATE DATABASE IF NOT EXISTS blog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_db;

-- Posts table
CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  author VARCHAR(100) NOT NULL,
  category VARCHAR(100) DEFAULT NULL,
  tags VARCHAR(255) DEFAULT NULL,
  cover_image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- For existing installations, run the following to add columns safely
-- (will error if column already exists; ignore such errors):
-- ALTER TABLE posts ADD COLUMN category VARCHAR(100) DEFAULT NULL AFTER author;
-- ALTER TABLE posts ADD COLUMN tags VARCHAR(255) DEFAULT NULL AFTER category;
-- ALTER TABLE posts ADD COLUMN cover_image VARCHAR(255) DEFAULT NULL AFTER tags;
