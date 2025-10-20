CREATE DATABASE IF NOT EXISTS venue_db;
USE venue_db;

-- Table for Locations
CREATE TABLE IF NOT EXISTS locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

-- Table for Bookings
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  location_id INT,
  venue_type VARCHAR(50),
  user_name VARCHAR(255) NOT NULL,
  booking_reason TEXT NULL,
  user_type VARCHAR(50),
  booked_date DATE,
  status VARCHAR(20) NOT NULL DEFAULT 'Pending',
  booking_group_id VARCHAR(50) NULL DEFAULT NULL,
  per_day_charge DECIMAL(10, 2) NULL DEFAULT 0.00,       -- NEW
  total_per_day_charge DECIMAL(10, 2) NULL DEFAULT 0.00,  -- NEW
  refundable_security DECIMAL(10, 2) NULL DEFAULT 0.00,   -- NEW
  application_form_cost DECIMAL(10, 2) NULL DEFAULT 0.00, -- NEW
  total_charges DECIMAL(10, 2) NULL DEFAULT 0.00,         -- NEW
  FOREIGN KEY (location_id) REFERENCES locations(id)
);

-- Optional: Add some locations if you haven't already
INSERT IGNORE INTO locations (id, name) VALUES
(1, 'South Delhi'),
(2, 'East Delhi'),
(3, 'Dallupura');
