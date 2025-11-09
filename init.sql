-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS wshop_api;
USE wshop_api;

-- Create places table WITH submitted_by
CREATE TABLE IF NOT EXISTS places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    rating DECIMAL(3, 2) NULL,
    submitted_by VARCHAR(100) NOT NULL,  -- ← NEW FIELD
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data WITH submitted_by
INSERT IGNORE INTO places (name, description, category, address, city, rating, submitted_by) VALUES
('Central Park Cafe', 'Cozy cafe with great coffee and pastries', 'cafe', '123 Park Avenue', 'New York', 4.5, 'Alice Johnson'),
('Louvre Museum', 'World''s largest art museum', 'museum', 'Rue de Rivoli', 'Paris', 4.8, 'Bob Smith'),
('Eiffel Tower', 'Iconic iron tower', 'landmark', 'Champ de Mars', 'Paris', 4.7, 'Carol Davis'),
('Brooklyn Bridge', 'Historic suspension bridge', 'landmark', 'Brooklyn Bridge', 'New York', 4.6, 'David Wilson'),
('Shakespeare & Company', 'Famous English-language bookstore', 'shop', '37 Rue de la Bûcherie', 'Paris', 4.9, 'Emma Brown'),
('Metropolitan Museum', 'Encyclopedic art museum', 'museum', '1000 5th Avenue', 'New York', 4.7, 'Frank Miller'),
('Notre-Dame Cathedral', 'Medieval Catholic cathedral', 'landmark', '6 Parvis Notre-Dame', 'Paris', 4.8, 'Grace Taylor'),
('Starbucks Reserve', 'Premium coffee experience', 'cafe', '61 9th Avenue', 'New York', 4.3, 'Henry Clark'),
('Galeries Lafayette', 'Luxury department store', 'shop', '40 Boulevard Haussmann', 'Paris', 4.6, 'Ivy Martinez'),
('Central Park', 'Urban park in Manhattan', 'park', 'Central Park', 'New York', 4.9, 'Jack Anderson');