CREATE DATABASE IF NOT EXISTS rehber;
USE rehber;

-- Mekanlar (Tarihi, Doğa, Park, Kaplıca)
CREATE TABLE places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category ENUM('Historical', 'Nature', 'Park', 'HotSpring') NOT NULL,
    description TEXT,
    lat DECIMAL(10, 8),
    lng DECIMAL(11, 8),
    image_main VARCHAR(255),
    image_gallery JSON,
    panorama_360 VARCHAR(255),
    ai_context TEXT,
    qr_code_path VARCHAR(255),
    popular_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Yol Üstü Durakları (AI Rehber için)
CREATE TABLE road_stops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_place_id INT,
    poi_name VARCHAR(255) NOT NULL,
    trigger_radius INT DEFAULT 100, -- Metre
    audio_script TEXT,
    FOREIGN KEY (parent_place_id) REFERENCES places(id) ON DELETE CASCADE
);

-- İşletmeler (Restoran, Otel)
CREATE TABLE businesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    contact_info TEXT,
    lat DECIMAL(10, 8),
    lng DECIMAL(11, 8),
    category ENUM('Restaurant', 'Hotel') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- İşletme Ürünleri
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2),
    description TEXT,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- Belediye Hizmetleri
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Ongoing', 'Completed') NOT NULL,
    image VARCHAR(255)
);

-- Etkinlikler
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kullanıcılar (Vatandaşlar)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    activation_code VARCHAR(100),
    is_active TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kullanıcı Ziyaret Geçmişi
CREATE TABLE user_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    place_id INT,
    visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
);

-- Sizden Gelenler (Paylaşımlar)
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    content TEXT,
    image_path VARCHAR(255),
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Duyurular
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
