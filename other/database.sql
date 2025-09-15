-- Tạo cơ sở dữ liệu mới
CREATE DATABASE IF NOT EXISTS bookstoredb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookstoredb;

-- Tạo bảng Users
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tạo bảng Books (thay thế cho LightNovels)
CREATE TABLE Books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    price DECIMAL(10,2) DEFAULT 0,
    status ENUM('Đang cập nhật', 'Đã hoàn thành', 'Tạm ngưng') DEFAULT 'Đang cập nhật',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tạo bảng Categories
CREATE TABLE Categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

-- Tạo bảng Book_Categories (thay thế cho Novel_Categories)
CREATE TABLE Book_Categories (
    book_id INT,
    category_id INT,
    PRIMARY KEY (book_id, category_id),
    FOREIGN KEY (book_id) REFERENCES Books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES Categories(category_id) ON DELETE CASCADE
);

-- Tạo bảng Book_Chapters (thay thế cho Chapters)
CREATE TABLE Book_Chapters (
    chapter_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    chapter_number INT,
    price DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES Books(book_id) ON DELETE CASCADE
);

-- Tạo bảng Reading_History
CREATE TABLE Reading_History (
    user_id INT,
    book_id INT,
    chapter_id INT,
    last_read TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, book_id, chapter_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES Books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES Book_Chapters(chapter_id) ON DELETE CASCADE
);

-- Tạo bảng Favorites
CREATE TABLE Favorites (
    user_id INT,
    book_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, book_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES Books(book_id) ON DELETE CASCADE
);

-- Tạo bảng Purchases
CREATE TABLE Purchases (
    purchase_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    price DECIMAL(10,2) NOT NULL,
    discount_applied DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES Books(book_id) ON DELETE CASCADE
);

-- Tạo bảng Comments
CREATE TABLE Comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES Books(book_id) ON DELETE CASCADE
);

-- Tạo tài khoản admin mặc định (password: admin123)
INSERT INTO Users (username, email, password_hash, role) 
VALUES ('admin', 'admin@example.com', 'admin123', 'admin'); 