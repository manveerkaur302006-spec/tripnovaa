-- TRIPNOVAA Database Schema + Seed Data
-- Import via phpMyAdmin or: mysql -u root < database/tripnovaa.sql

CREATE DATABASE IF NOT EXISTS tripnovaa_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tripnovaa_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS admin_logs, tour_booking_seats, tour_itinerary, tour_seats, tour_bookings, tour_packages,
    driver_offers, trip_assignments, transport_bookings, trip_plans, ride_feedback, rides, saved_locations,
    hotel_bookings, restaurants, messages, payments, wallet_transactions, feedback, otp_codes, captain_profiles,
    transport_inventory, offers, popular_destinations, platform_settings, reward_milestones, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','customer','captain') NOT NULL DEFAULT 'customer',
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive','pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE captain_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    vehicle_type ENUM('bike','auto','mini','sedan','suv','bus') DEFAULT 'sedan',
    vehicle_number VARCHAR(20),
    vehicle_model VARCHAR(100),
    license_number VARCHAR(50),
    rating DECIMAL(3,2) DEFAULT 4.50,
    total_trips INT DEFAULT 0,
    is_online TINYINT(1) DEFAULT 0,
    wallet_balance DECIMAL(12,2) DEFAULT 0,
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    code VARCHAR(6) NOT NULL,
    role VARCHAR(20) DEFAULT 'customer',
    expires_at DATETIME NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE saved_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(50) NOT NULL,
    address VARCHAR(255) NOT NULL,
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE rides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    captain_id INT DEFAULT NULL,
    pickup_address VARCHAR(255) NOT NULL,
    drop_address VARCHAR(255) NOT NULL,
    pickup_lat DECIMAL(10,7),
    pickup_lng DECIMAL(10,7),
    drop_lat DECIMAL(10,7),
    drop_lng DECIMAL(10,7),
    vehicle_type ENUM('bike','auto','mini','sedan') NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    status ENUM('searching','rider_assigned','on_ride','payment_pending','completed','cancelled') DEFAULT 'searching',
    ride_otp VARCHAR(4),
    payment_method VARCHAR(50) DEFAULT 'cash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (captain_id) REFERENCES users(id)
);

CREATE TABLE ride_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_id INT NOT NULL,
    rating INT NOT NULL,
    issue_tags JSON,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE
);

CREATE TABLE trip_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    from_city VARCHAR(100) NOT NULL,
    to_city VARCHAR(100) NOT NULL,
    travelers INT DEFAULT 1,
    start_date DATE,
    end_date DATE,
    budget_min DECIMAL(12,2),
    budget_max DECIMAL(12,2),
    places TEXT,
    total_cost DECIMAL(12,2) DEFAULT 0,
    advance_paid DECIMAL(12,2) DEFAULT 0,
    status ENUM('draft','open','offers_received','booked','in_progress','completed','cancelled') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id)
);

CREATE TABLE transport_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('bus','train','flight') NOT NULL,
    operator VARCHAR(100) NOT NULL,
    from_city VARCHAR(100) NOT NULL,
    to_city VARCHAR(100) NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    rating DECIMAL(3,2) DEFAULT 4.0,
    vehicle_class VARCHAR(50)
);

CREATE TABLE transport_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trip_plan_id INT DEFAULT NULL,
    type ENUM('bus','train','flight') NOT NULL,
    operator VARCHAR(100),
    from_city VARCHAR(100),
    to_city VARCHAR(100),
    departure_time TIME,
    arrival_time TIME,
    travel_date DATE,
    price DECIMAL(10,2),
    pnr VARCHAR(20),
    passengers INT DEFAULT 1,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (trip_plan_id) REFERENCES trip_plans(id) ON DELETE SET NULL
);

CREATE TABLE trip_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_plan_id INT NOT NULL,
    captain_id INT NOT NULL,
    status ENUM('pending','accepted','in_progress','completed') DEFAULT 'pending',
    commission_amount DECIMAL(12,2) DEFAULT 0,
    FOREIGN KEY (trip_plan_id) REFERENCES trip_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (captain_id) REFERENCES users(id)
);

CREATE TABLE driver_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_plan_id INT NOT NULL,
    captain_id INT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    services_json JSON,
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_plan_id) REFERENCES trip_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (captain_id) REFERENCES users(id)
);

CREATE TABLE tour_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    captain_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    days INT DEFAULT 3,
    nights INT DEFAULT 2,
    price DECIMAL(12,2) NOT NULL,
    seats_total INT DEFAULT 40,
    start_date DATE,
    end_date DATE,
    image_url VARCHAR(500),
    highlights TEXT,
    rating DECIMAL(3,2) DEFAULT 4.5,
    category VARCHAR(50) DEFAULT 'group',
    status ENUM('upcoming','active','completed') DEFAULT 'upcoming',
    FOREIGN KEY (captain_id) REFERENCES users(id)
);

CREATE TABLE tour_seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    status ENUM('available','booked','selected') DEFAULT 'available',
    FOREIGN KEY (tour_id) REFERENCES tour_packages(id) ON DELETE CASCADE
);

CREATE TABLE tour_itinerary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT NOT NULL,
    day_number INT NOT NULL,
    title VARCHAR(200),
    description TEXT,
    FOREIGN KEY (tour_id) REFERENCES tour_packages(id) ON DELETE CASCADE
);

CREATE TABLE tour_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tour_id INT NOT NULL,
    seats_json JSON NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    advance_paid DECIMAL(12,2) DEFAULT 0,
    remaining DECIMAL(12,2) DEFAULT 0,
    status ENUM('pending','confirmed','in_progress','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (tour_id) REFERENCES tour_packages(id)
);

CREATE TABLE hotel_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_name VARCHAR(200) NOT NULL,
    location VARCHAR(200),
    checkin DATE NOT NULL,
    checkout DATE NOT NULL,
    guests INT DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    cuisine VARCHAR(100),
    rating DECIMAL(3,2) DEFAULT 4.0,
    location VARCHAR(200),
    is_veg TINYINT(1) DEFAULT 1,
    image_url VARCHAR(500)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    ride_id INT DEFAULT NULL,
    trip_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    lat DECIMAL(10,7) DEFAULT NULL,
    lng DECIMAL(10,7) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reference_type VARCHAR(50) NOT NULL,
    reference_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    method VARCHAR(50) DEFAULT 'online',
    cashfree_order_id VARCHAR(100),
    status ENUM('pending','success','failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    captain_id INT NOT NULL,
    type ENUM('credit','debit') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (captain_id) REFERENCES users(id)
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    rating INT,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    code VARCHAR(50),
    discount_type ENUM('percent','flat','cashback') DEFAULT 'percent',
    value DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) DEFAULT 'all',
    valid_until DATE,
    is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE popular_destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100),
    image_url VARCHAR(500),
    price DECIMAL(12,2),
    rating DECIMAL(3,2) DEFAULT 4.5,
    duration VARCHAR(50)
);

CREATE TABLE platform_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT
);

CREATE TABLE reward_milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    milestone_label VARCHAR(20) NOT NULL,
    target_downloads BIGINT NOT NULL,
    color VARCHAR(20),
    rewards_json JSON,
    sort_order INT DEFAULT 0
);

CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Password for all demo users: password (bcrypt hash below)
-- admin123 also works after first login via register; demo hash is for 'password'
SET @pwd = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

INSERT INTO users (name, email, phone, password_hash, role) VALUES
('Admin User', 'admin@tripnovaa.com', '9000000001', @pwd, 'admin'),
('Arjun Sharma', 'arjun@tripnovaa.com', '8870463212', @pwd, 'customer'),
('Priya Reddy', 'priya@tripnovaa.com', '9876543210', @pwd, 'customer'),
('Rohit Kumar', 'rohit@tripnovaa.com', '9123456789', @pwd, 'customer'),
('Rohit Thakur', 'rohit.captain@tripnovaa.com', '9500465712', @pwd, 'captain'),
('Suresh Yadav', 'suresh@tripnovaa.com', '9988776655', @pwd, 'captain'),
('Amit Thakur', 'amit@tripnovaa.com', '9876501234', @pwd, 'captain'),
('Ramesh Kumar', 'ramesh@tripnovaa.com', '9765432109', @pwd, 'captain'),
('Sarik Kumar', 'sarik@tripnovaa.com', '9654321098', @pwd, 'captain');

INSERT INTO captain_profiles (user_id, vehicle_type, vehicle_number, vehicle_model, license_number, rating, total_trips, is_online, wallet_balance, bio) VALUES
(5, 'bike', 'KA 01 JK 1234', 'TVS Raider 125', 'DL-1234567890', 4.80, 235, 1, 6240.00, 'Himachal Local Expert'),
(6, 'auto', 'KA 02 AB 5678', 'Bajaj RE', 'DL-2345678901', 4.60, 180, 1, 4500.00, 'Experienced city driver'),
(7, 'sedan', 'HP 01 AB 1234', 'Innova Crysta', 'DL-3456789012', 4.90, 320, 1, 15200.00, 'Manali Local Expert'),
(8, 'bus', 'AP 39 Z 9999', 'AC Luxury Bus 50 Seater', 'DL-4567890123', 4.70, 150, 0, 8900.00, 'Group tour specialist'),
(9, 'mini', 'KA 03 CD 9012', 'Maruti Swift', 'DL-5678901234', 4.50, 95, 1, 3200.00, 'Quick ride captain');

INSERT INTO saved_locations (user_id, label, address, lat, lng) VALUES
(2, 'Home', 'Indiranagar, Bengaluru', 12.9784, 77.6408),
(2, 'Work', 'Manyata Tech Park, Bengaluru', 13.0358, 77.5970),
(2, 'Airport', 'Kempegowda International Airport', 13.1986, 77.7066);

INSERT INTO transport_inventory (type, operator, from_city, to_city, departure_time, arrival_time, price, rating, vehicle_class) VALUES
('bus', 'HPTDC Volvo', 'Bangalore', 'Manali', '20:00:00', '14:00:00', 1460.00, 4.5, 'AC Sleeper'),
('bus', 'SRS Travels', 'Bangalore', 'Manali', '21:30:00', '16:00:00', 2400.00, 4.7, 'Volvo AC'),
('bus', 'Zingbus Plus', 'Bangalore', 'Manali', '22:00:00', '15:30:00', 2100.00, 4.6, 'AC Seater'),
('bus', 'APSRTC', 'Vijayawada', 'Hyderabad', '08:00:00', '14:00:00', 750.00, 4.3, 'Non-AC'),
('bus', 'Sree Kaleswari', 'Vijayawada', 'Hyderabad', '10:30:00', '16:30:00', 850.00, 4.4, 'AC'),
('train', 'KCG Goutami Express', 'Vijayawada', 'Tirupati', '18:45:00', '05:30:00', 680.00, 4.2, 'Sleeper'),
('train', 'Yesvantpur Express', 'Bangalore', 'Manali', '22:15:00', '18:00:00', 1200.00, 4.5, '3AC'),
('flight', 'IndiGo', 'Bangalore', 'Manali', '06:00:00', '09:30:00', 4500.00, 4.8, 'Economy'),
('flight', 'Air India', 'Bangalore', 'Delhi', '14:00:00', '16:30:00', 5200.00, 4.6, 'Economy'),
('bus', 'HPFC Volvo', 'Delhi', 'Manali', '19:00:00', '10:00:00', 980.00, 4.4, 'AC Sleeper');

INSERT INTO popular_destinations (name, country, image_url, price, rating, duration) VALUES
('Bali', 'Indonesia', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=400', 50999, 4.8, '5 Days / 4 Nights'),
('Manali', 'India', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=400', 12999, 4.5, '3 Days / 2 Nights'),
('Paris', 'France', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=400', 85999, 4.9, '7 Days / 6 Nights'),
('Dubai', 'UAE', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=400', 52500, 4.7, '5 Days / 4 Nights'),
('Goa', 'India', 'https://images.unsplash.com/photo-1512343879784-a960bf40e7e2?w=400', 8999, 4.6, '3 Days / 2 Nights'),
('Shimla', 'India', 'https://images.unsplash.com/photo-1597074866921-dc06a2318833?w=400', 11999, 4.5, '4 Days / 3 Nights'),
('Leh Ladakh', 'India', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=400', 24999, 4.9, '7 Days / 6 Nights');

INSERT INTO offers (title, code, discount_type, value, category, valid_until) VALUES
('Flat 25% OFF on Flights', 'FLY25', 'percent', 25, 'flights', '2026-12-31'),
('Flat 10% OFF on Outstation Trips', 'TRIP10', 'percent', 10, 'trips', '2026-12-31'),
('Rs 500 OFF on Local Sightseeing', 'LOCAL500', 'flat', 500, 'tours', '2026-12-31'),
('Up to 15% Cashback on Round Trip', 'CASH15', 'cashback', 15, 'all', '2026-12-31'),
('Up to 10% OFF Buses', 'TRIPNOVA', 'percent', 10, 'bus', '2026-12-31');

INSERT INTO restaurants (name, cuisine, rating, location, is_veg, image_url) VALUES
('Exotic Thai', 'Thai', 4.5, 'Benz Circle, Vijayawada', 0, 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=400'),
('Ganga Residency Restaurant', 'Indian', 4.3, 'Nellore', 1, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400'),
('Spice Garden', 'North Indian', 4.6, 'Bengaluru', 1, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400'),
('Coastal Delights', 'Seafood', 4.4, 'Goa', 0, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=400'),
('Mountain Cafe', 'Continental', 4.7, 'Manali', 1, 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=400');

INSERT INTO platform_settings (setting_key, setting_value) VALUES
('download_count', '23400000'),
('platform_fee_percent', '5'),
('captain_commission_percent', '80');

INSERT INTO reward_milestones (milestone_label, target_downloads, color, rewards_json, sort_order) VALUES
('1M', 1000000, '#9B59B6', '["T-Shirt","Cap","Water Bottle"]', 1),
('10M', 10000000, '#8E44AD', '["Headphones","Power Bank"]', 2),
('20M', 20000000, '#3498DB', '["Smartwatch","Backpack"]', 3),
('30M', 30000000, '#1ABC9C', '["Tablet","Bluetooth Speaker"]', 4),
('50M', 50000000, '#27AE60', '["Smartphone","Kitchen Appliance"]', 5),
('100M', 100000000, '#E74C3C', '["Laptop","Motorcycle"]', 6);

INSERT INTO tour_packages (captain_id, title, destination, days, nights, price, seats_total, start_date, end_date, image_url, highlights, rating, category, status) VALUES
(8, 'Vijayawada to Kasi Pilgrimage', 'Varanasi', 10, 9, 12999, 50, '2026-08-15', '2026-08-25', 'https://images.unsplash.com/photo-1567593814470-9d1a2c4e0e8a?w=400', 'Kasi Vishwanath Temple,Ganga Aarti,Sarnath', 4.8, 'pilgrimage', 'upcoming'),
(8, 'Manali Adventure Tour', 'Manali', 5, 4, 15999, 40, '2026-06-20', '2026-06-25', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=400', 'Solang Valley,Rohtang Pass,Old Manali', 4.7, 'adventure', 'upcoming'),
(7, 'Goa Beach Holiday', 'Goa', 4, 3, 9999, 30, '2026-07-10', '2026-07-14', 'https://images.unsplash.com/photo-1512343879784-a960bf40e7e2?w=400', 'Beaches,Water Sports,Nightlife', 4.6, 'holiday', 'upcoming'),
(8, 'Shimla Hill Station', 'Shimla', 3, 2, 7999, 45, '2026-05-25', '2026-05-28', 'https://images.unsplash.com/photo-1597074866921-dc06a2318833?w=400', 'Mall Road,Kufri,Ridge', 4.5, 'holiday', 'upcoming'),
(7, 'Leh Ladakh Expedition', 'Leh', 7, 6, 25999, 25, '2026-09-01', '2026-09-08', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=400', 'Pangong Lake,Khardung La,Nubra Valley', 4.9, 'adventure', 'upcoming');

-- Generate seats for tours 1-5
INSERT INTO tour_seats (tour_id, seat_number, status)
SELECT t.id, s.n, IF(RAND() > 0.7, 'booked', 'available')
FROM tour_packages t
CROSS JOIN (
    SELECT '1A' n UNION SELECT '1B' UNION SELECT '2A' UNION SELECT '2B' UNION SELECT '3A' UNION SELECT '3B'
    UNION SELECT '4A' UNION SELECT '4B' UNION SELECT '5A' UNION SELECT '5B' UNION SELECT '6A' UNION SELECT '6B'
    UNION SELECT '7A' UNION SELECT '7B' UNION SELECT '8A' UNION SELECT '8B' UNION SELECT '9A' UNION SELECT '9B'
    UNION SELECT '10A' UNION SELECT '10B' UNION SELECT '11A' UNION SELECT '11B' UNION SELECT '12A' UNION SELECT '12B'
) s;

INSERT INTO tour_itinerary (tour_id, day_number, title, description) VALUES
(1, 1, 'Departure from Vijayawada', 'Board AC luxury bus, overnight journey'),
(1, 2, 'Arrive Varanasi', 'Check-in, evening Ganga Aarti'),
(2, 1, 'Arrive Manali', 'Check-in at hotel, local market visit'),
(2, 2, 'Solang Valley', 'Paragliding, skiing, adventure activities'),
(2, 3, 'Rohtang Pass', 'Snow activities and scenic views');

INSERT INTO trip_plans (customer_id, from_city, to_city, travelers, start_date, end_date, budget_min, budget_max, places, total_cost, status) VALUES
(2, 'Bangalore', 'Manali', 2, '2026-05-20', '2026-05-24', 15000, 25000, 'Solang Valley,Rohtang Pass,Old Manali', 19800, 'offers_received'),
(3, 'Vijayawada', 'Kochi', 4, '2026-06-01', '2026-06-07', 20000, 35000, 'Backwaters,Beaches', 28000, 'open');

INSERT INTO driver_offers (trip_plan_id, captain_id, price, services_json, status) VALUES
(1, 7, 4500, '["AC","Local Guide","Sightseeing","Pickup & Drop"]', 'pending'),
(1, 5, 4200, '["AC","Sightseeing"]', 'pending'),
(2, 7, 12000, '["AC Car","6 Seats","Local Guide","Pickup & Drop"]', 'pending');

INSERT INTO rides (customer_id, captain_id, pickup_address, drop_address, vehicle_type, fare, status, ride_otp, payment_method) VALUES
(2, 5, 'Current Location, Bengaluru', 'Manyata Tech Park', 'bike', 45, 'completed', '1234', 'cash');

INSERT INTO wallet_transactions (captain_id, type, amount, description) VALUES
(5, 'credit', 45, 'Ride #1 completed'),
(5, 'credit', 120, 'Ride fare credit'),
(7, 'credit', 4500, 'Trip assignment payment');
