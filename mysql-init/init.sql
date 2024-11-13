
-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS petadoption;
USE petadoption;

-- Users Table
CREATE TABLE IF NOT EXISTS Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone_number VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Pets Table
CREATE TABLE IF NOT EXISTS Pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT,
    name VARCHAR(50) NOT NULL,
    species VARCHAR(50) NOT NULL,
    breed VARCHAR(50),
    age INT,
    gender ENUM('male', 'female', 'unknown'),
    description TEXT,
    status ENUM('available', 'adopted', 'pending') DEFAULT 'available',
    size ENUM('small', 'medium', 'large'),
    color VARCHAR(50),
    special_needs BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES Users(id)
);

-- AdoptionApplications Table
CREATE TABLE IF NOT EXISTS AdoptionApplications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT,
    applicant_id INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    living_situation TEXT,
    experience TEXT,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES Pets(id),
    FOREIGN KEY (applicant_id) REFERENCES Users(id)
);

-- Messages Table
CREATE TABLE IF NOT EXISTS Messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    adoption_application_id INT,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES Users(id),
    FOREIGN KEY (receiver_id) REFERENCES Users(id),
    FOREIGN KEY (adoption_application_id) REFERENCES AdoptionApplications(id)
);

-- PetImages Table
CREATE TABLE IF NOT EXISTS PetImages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT,
    image_url VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES Pets(id)
);

-- UserPreferences Table
CREATE TABLE IF NOT EXISTS UserPreferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    preferred_species VARCHAR(50),
    preferred_breed VARCHAR(50),
    preferred_age_min INT,
    preferred_age_max INT,
    preferred_size ENUM('small', 'medium', 'large'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id)
);
