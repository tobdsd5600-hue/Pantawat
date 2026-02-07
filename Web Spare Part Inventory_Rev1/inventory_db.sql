-- สร้างฐานข้อมูลชื่อ inventory_system
CREATE DATABASE IF NOT EXISTS inventory_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_system;

-- 1. ตารางผู้ใช้งาน (Users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    en VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    role VARCHAR(50) NOT NULL, -- Admin, Manager, Technician
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'Active'
);

-- เพิ่ม User เริ่มต้น (Default)
INSERT INTO users (en, name, position, role, username, password, status) VALUES 
('001', 'Admin User', 'System Admin', 'Admin', 'admin', 'admin', 'Active'),
('002', 'Manager User', 'Stock Manager', 'Manager', 'manager', '1234', 'Active'),
('003', 'Tech User', 'Technician', 'Technician', 'tech', '1234', 'Active');

-- 2. ตารางสินค้า/อะไหล่ (Parts)
CREATE TABLE IF NOT EXISTS parts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    part_no VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    qty INT DEFAULT 0,
    min_level INT DEFAULT 5,
    location VARCHAR(100),
    supplier VARCHAR(100),
    status VARCHAR(50) DEFAULT 'In Stock',
    image LONGTEXT -- เก็บรูปภาพเป็น Base64 String (เพื่อให้ง่ายต่อการ migrate จาก code เดิม)
);

-- 3. ตารางคำขอเบิก (Requests)
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    req_id VARCHAR(50),
    part_no VARCHAR(50),
    part_name VARCHAR(150),
    qty INT,
    requester VARCHAR(100),
    en VARCHAR(50),
    station VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pending', -- Pending, Approved, Rejected
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    details JSON, -- เก็บข้อมูลเพิ่มเติมเช่น machine_sn, reason, remark
    action_by VARCHAR(100), -- คนที่กดอนุมัติ/ปฏิเสธ
    action_time DATETIME,
    note TEXT -- เหตุผลการปฏิเสธ
);

-- 4. ตารางใบสั่งซื้อ (Purchase Orders)
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id VARCHAR(50),
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    part_no VARCHAR(50),
    part_name VARCHAR(150),
    qty INT,
    requester VARCHAR(100),
    reason VARCHAR(100),
    supplier VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pending Approval',
    remark TEXT
);

-- 5. ตารางประวัติการแก้ไขสต็อก (Edit History Log)
CREATE TABLE IF NOT EXISTS history_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time DATETIME DEFAULT CURRENT_TIMESTAMP,
    part_no VARCHAR(50),
    part_name VARCHAR(150),
    changes TEXT, -- รายละเอียดการเปลี่ยนแปลง เช่น Qty: 10->20
    editor VARCHAR(100) -- ชื่อคนทำรายการ
);