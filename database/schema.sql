CREATE DATABASE IF NOT EXISTS gg_hosiery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gg_hosiery;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('owner','admin','employee') NOT NULL DEFAULT 'employee',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS company_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(160) NOT NULL DEFAULT 'GG Hosiery',
    gstin VARCHAR(32) NULL,
    address TEXT NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    invoice_prefix VARCHAR(20) NOT NULL DEFAULT 'GGH',
    default_gst_rate DECIMAL(5,2) NOT NULL DEFAULT 18.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    gstin VARCHAR(32) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customers_search (name, phone, city)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    gstin VARCHAR(32) NULL,
    address TEXT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    name VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NULL,
    designation VARCHAR(100) NULL,
    salary DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT UNSIGNED NULL,
    sku VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    category VARCHAR(100) NOT NULL,
    size VARCHAR(60) NULL,
    color VARCHAR(60) NULL,
    image_path VARCHAR(255) NULL,
    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    sale_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    gst_rate DECIMAL(5,2) NOT NULL DEFAULT 18.00,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_products_search (sku, name, category)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    location VARCHAR(120) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventory_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    order_number VARCHAR(40) NOT NULL UNIQUE,
    status ENUM('draft','confirmed','packed','delivered','cancelled') NOT NULL DEFAULT 'draft',
    payment_status ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    gst_rate DECIMAL(5,2) NOT NULL,
    line_total DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NULL,
    customer_id INT UNSIGNED NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_date DATE NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    cgst DECIMAL(12,2) NOT NULL DEFAULT 0,
    sgst DECIMAL(12,2) NOT NULL DEFAULT 0,
    igst DECIMAL(12,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('draft','sent','paid','cancelled') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoices_customer FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS invoice_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT UNSIGNED NOT NULL,
    description VARCHAR(220) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    gst_rate DECIMAL(5,2) NOT NULL,
    line_total DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    method ENUM('cash','upi','bank','card','credit') NOT NULL DEFAULT 'cash',
    paid_at DATETIME NOT NULL,
    reference VARCHAR(120) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    title VARCHAR(160) NOT NULL,
    body TEXT NULL,
    type ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
    read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(60) NULL,
    user_agent VARCHAR(250) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity_created (created_at),
    INDEX idx_activity_action (action)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wholesale_inquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(160) NOT NULL,
    contact_name VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(190) NULL,
    city VARCHAR(100) NULL,
    product_interest VARCHAR(180) NULL,
    monthly_volume VARCHAR(80) NULL,
    message TEXT NULL,
    status ENUM('new','contacted','converted','closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    subject VARCHAR(180) NULL,
    message TEXT NOT NULL,
    status ENUM('new','read','closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS website_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firebase_uid VARCHAR(160) NOT NULL UNIQUE,
    name VARCHAR(160) NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    photo_url VARCHAR(255) NULL,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO company_settings (business_name)
SELECT 'GG Hosiery'
WHERE NOT EXISTS (SELECT 1 FROM company_settings);
