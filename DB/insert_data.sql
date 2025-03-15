

-- INSERT INTO Users (user_id, first_name, last_name, email, password, address, phone_number, role, created_at, updated_at, status) VALUES
-- (1, 'Dinuka', 'Prasad', 'dfnulagprasad.pr9@gmail.com', '$2y$10$VAPR4Z7h0gHHSXaZ7W8kJYKXDVKLZa B12e4f5wmG...', 'Namingarna_Ambampola_Mesiripura', '0761986867', 'user', '2025-03-11 17:09:00', '2025-03-11 17:09:00', 'active'),
-- (2, 'Shukya', 'Perindi', 'shuki@gmail.com', '$2y$10$dMms6volQ/owlcSAiRPxIB.YYzw2qF8fbkbjXBp6sw...', 'Narammala', '0764545454', 'user', '2025-03-11 17:09:37', '2025-03-11 17:09:37', 'active'),
-- (3, 'Hexner', 'Dog', 'hexner@gmail.com', '$2y$10$shwgB0WdWrI3xMrR1UTrjq6iO72pqa3ihLn09/wryZ...', 'Narammala', '0974545454', 'admin', '2025-03-11 17:10:11', '2025-03-11 17:10:28', 'active');

-- Insert Data into GarbageCategory Table
INSERT INTO GarbageCategory (category_name) VALUES
('Plastic'),
('Metal'),
('Paper'),
('Glass'),
('Organic'),
('Electronic Waste'),
('Textiles'),
('Other');

-- Insert Data into Advertisements Table
INSERT INTO Advertisements (seller_id, category_id, weight, price, description, status) VALUES
(1, 1, 50.00, 10.00, 'Clean plastic bottles', 'pending'),
(2, 2, 20.00, 15.00, 'Scrap metal pieces', 'pending'),
(3, 3, 30.00, 5.00, 'Old newspapers', 'pending');

-- Insert Data into Deals Table
INSERT INTO Deals (buyer_id, ad_id, deal_status, deal_price) VALUES
(2, 1, 'pending', 10.00),
(3, 2, 'pending', 15.00), 
(1, 3, 'pending', 5.00); 

-- Insert Data into Feedback Table
INSERT INTO Feedback (deal_id, from_user_id, to_user_id, rating, comment) VALUES
(1, 1, 2, 5, 'Great buyer, smooth transaction!'), 
(2, 2, 3, 4, 'Good buyer, fast payment.'),       
(3, 3, 1, 5, 'Excellent buyer, highly recommended!'); 

-- Insert Data into GarbageCollectionSchedule Table
INSERT INTO GarbageCollectionSchedule (location, collection_date, collection_time) VALUES
('Main Street', '2023-12-01', '08:00:00'), 
('Eim Street', '2023-12-02', '09:00:00'),  
('Narammala', '2023-12-03', '10:00:00');   

-- Insert Data into Notifications Table
INSERT INTO Notifications (user_id, message, status) VALUES
(1, 'Your advertisement for Plastic has a new deal request.', 'unread'), 
(2, 'Your advertisement for Metal has a new deal request.', 'unread'),  
(3, 'Your advertisement for Paper has a new deal request.', 'unread');   

-- Insert Data into GarbageRatings Table
INSERT INTO GarbageRatings (buyer_id, category_id, price_per_kg) VALUES
(1, 1, 10.50), 
(2, 2, 15.00), 
(3, 3, 5.00);  