CREATE DATABASE venue_db;
USE venue_db;

-- Table for Locations
CREATE TABLE locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

-- Table for Bookings
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  location_id INT,
  venue_type VARCHAR(50),
  user_type VARCHAR(50),
  booked_date DATE,
  FOREIGN KEY (location_id) REFERENCES locations(id)
);
