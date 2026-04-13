CREATE DATABASE IF NOT EXISTS techstock_db;
USE techstock_db;

-- Users (Owner, Employee, Buyer)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('owner','employee','buyer') DEFAULT 'buyer',
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Shops
CREATE TABLE IF NOT EXISTS shops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150),
    address TEXT,
    phone VARCHAR(20),
    whatsapp VARCHAR(20),
    email VARCHAR(100),
    description TEXT,
    logo VARCHAR(255),
    subscription ENUM('free','basic','premium') DEFAULT 'free',
    subscription_expires DATE,
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id)
);

-- Employee-Shop Assignment
CREATE TABLE IF NOT EXISTS shop_employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    employee_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (shop_id, employee_id)
);

-- Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    added_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    brand VARCHAR(100),
    cpu VARCHAR(100),
    ram VARCHAR(50),
    storage VARCHAR(100),
    gpu VARCHAR(100),
    display VARCHAR(100),
    os VARCHAR(100),
    condition_type ENUM('new','used','refurbished') DEFAULT 'used',
    price DECIMAL(12,2) NOT NULL,
    original_price DECIMAL(12,2),
    description TEXT,
    image1 VARCHAR(255),
    image2 VARCHAR(255),
    image3 VARCHAR(255),
    status ENUM('available','reserved','sold') DEFAULT 'available',
    featured TINYINT DEFAULT 0,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id),
    FOREIGN KEY (added_by) REFERENCES users(id)
);

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    shop_id INT NOT NULL,
    buyer_id INT,
    buyer_name VARCHAR(100) NOT NULL,
    buyer_phone VARCHAR(20) NOT NULL,
    buyer_email VARCHAR(100),
    message TEXT,
    status ENUM('pending','confirmed','delivered','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (shop_id) REFERENCES shops(id)
);

-- Audit Log
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Featured Listings
CREATE TABLE IF NOT EXISTS featured_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    shop_id INT NOT NULL,
    expires_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (shop_id) REFERENCES shops(id)
);

-- Default Owner
INSERT INTO users (full_name, email, phone, password, role) VALUES
('TechStock Admin', 'admin@techstock.co.ke', '0700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner');

-- Sample Shop
INSERT INTO shops (owner_id, name, location, phone, whatsapp, description, status) VALUES
(1, 'TechHub Nairobi', 'Nairobi CBD', '0712345678', '254712345678', 'Premium laptops and desktops at the best prices in Nairobi.', 'active');

-- Sample Employee
INSERT INTO users (full_name, email, phone, password, role) VALUES
('John Mwangi', 'john@techstock.co.ke', '0711111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee');

-- Assign employee to shop
INSERT INTO shop_employees (shop_id, employee_id) VALUES (1, 2);

-- Sample Products
INSERT INTO products (shop_id, added_by, title, brand, cpu, ram, storage, gpu, condition_type, price, original_price, status, featured) VALUES
(1, 1, 'HP EliteBook 840 G6', 'HP', 'Intel Core i5-8265U', '8GB DDR4', '256GB SSD', 'Intel UHD 620', 'used', 45000, 55000, 'available', 1),
(1, 1, 'Dell Latitude 5490', 'Dell', 'Intel Core i7-8650U', '16GB DDR4', '512GB SSD', 'Intel UHD 620', 'refurbished', 65000, 80000, 'available', 1),
(1, 1, 'Lenovo ThinkPad T480', 'Lenovo', 'Intel Core i5-8350U', '8GB DDR4', '256GB SSD', 'Intel UHD 620', 'used', 42000, 50000, 'available', 0),
(1, 1, 'MacBook Pro 2019 13"', 'Apple', 'Intel Core i5-8257U', '8GB LPDDR3', '256GB SSD', 'Intel Iris Plus 640', 'used', 95000, 120000, 'available', 1),
(1, 1, 'HP ProBook 450 G7', 'HP', 'Intel Core i5-10210U', '8GB DDR4', '1TB HDD + 128GB SSD', 'NVIDIA MX250 2GB', 'new', 78000, 85000, 'available', 0),
(1, 1, 'Dell XPS 15 9500', 'Dell', 'Intel Core i7-10750H', '16GB DDR4', '512GB NVMe SSD', 'NVIDIA GTX 1650 Ti 4GB', 'new', 145000, 160000, 'available', 1);
