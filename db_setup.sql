-- SQL Injection Lab Database Setup
-- Execute this script in phpMyAdmin or MySQL CLI

-- Create database
CREATE DATABASE IF NOT EXISTS sqli_lab_db;
USE sqli_lab_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL
);

-- Insert sample users
INSERT INTO users (username, password) VALUES
('admin', 'pass123'),
('test', 'user456'),
('guest', 'guest789');

-- Create secret_keys table for sensitive data
CREATE TABLE IF NOT EXISTS secret_keys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    secret_info TEXT NOT NULL
);

-- Insert high-value secret
INSERT INTO secret_keys (secret_info) VALUES
('The production API key is PK-XYZ-789-DEF');

-- Display tables to verify
SELECT 'Users table created:' AS status;
SELECT * FROM users;
SELECT 'Secret keys table created:' AS status;
SELECT * FROM secret_keys;
