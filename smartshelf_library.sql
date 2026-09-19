-- =====================================================================
--  SmartShelf - Library Management System
--  Complete MySQL backup (schema + sample data)
--  Compatible with XAMPP / MariaDB / MySQL 5.7+
--
--  HOW TO IMPORT:
--    1. Open phpMyAdmin (http://localhost/phpmyadmin)
--    2. Click "Import" -> choose this file -> Go
--    (This script creates the database automatically.)
--
--  Default accounts (password shown in brackets):
--    admin@smartshelf.lk      (admin123)   -> Administrator
--    librarian@smartshelf.lk  (lib123)     -> Librarian
--    member@smartshelf.lk     (member123)  -> Member
--  All other seeded members also use the password: member123
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
--  Database
-- ---------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `smartshelf_library`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `smartshelf_library`;

-- ---------------------------------------------------------------------
--  Table: users
--  Stores administrators, librarians and members.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id`     INT(11) NOT NULL AUTO_INCREMENT,
  `fullname`    VARCHAR(120) NOT NULL,
  `email`       VARCHAR(150) NOT NULL,
  `password`    VARCHAR(255) NOT NULL,
  `role`        ENUM('admin','librarian','member') NOT NULL DEFAULT 'member',
  `phone`       VARCHAR(20) DEFAULT NULL,
  `address`     VARCHAR(255) DEFAULT NULL,
  `avatar`      VARCHAR(255) DEFAULT NULL,
  `status`      ENUM('active','suspended') NOT NULL DEFAULT 'active',
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password hashes below are real bcrypt hashes generated with password_hash().
--   admin123  -> $2y$10$4PDNwkK9EvovOK..XtQLVOcWD.lkCHdQDSC36Az.XVQ0TfehLuNq2
--   lib123    -> $2y$10$VLw1aTgXmQaW1TRuRYwOIui1iCDhIZIi2sQIjRoQFJbHIr0nyAU66
--   member123 -> $2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2
INSERT INTO `users` (`user_id`,`fullname`,`email`,`password`,`role`,`phone`,`address`,`status`,`created_at`) VALUES
(1,'System Administrator','admin@smartshelf.lk','$2y$10$4PDNwkK9EvovOK..XtQLVOcWD.lkCHdQDSC36Az.XVQ0TfehLuNq2','admin','0771234567','Colombo 03','active','2025-01-05 09:00:00'),
(2,'Nimali Perera','librarian@smartshelf.lk','$2y$10$VLw1aTgXmQaW1TRuRYwOIui1iCDhIZIi2sQIjRoQFJbHIr0nyAU66','librarian','0712223344','Kandy','active','2025-01-06 10:15:00'),
(3,'Kasun Silva','member@smartshelf.lk','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','member','0763334455','Galle','active','2025-01-10 11:30:00'),
(4,'Ishara Fernando','ishara@example.com','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','member','0701112233','Negombo','active','2025-01-12 08:45:00'),
(5,'Dilshan Jayawardena','dilshan@example.com','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','member','0759998877','Matara','active','2025-01-15 14:20:00'),
(6,'Tharushi Bandara','tharushi@example.com','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','member','0727776655','Kurunegala','active','2025-01-18 16:05:00'),
(7,'Ravindu Gunasekara','ravindu@example.com','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','member','0784443322','Jaffna','active','2025-02-01 09:10:00'),
(8,'Sachini Wickramasinghe','sachini@example.com','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','member','0716665544','Anuradhapura','active','2025-02-05 13:25:00'),
(9,'Menaka Rajapaksha','menaka@example.com','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','member','0775554433','Badulla','active','2025-02-09 10:40:00'),
(10,'Hashan Senanayake','hashan@example.com','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','member','0702221100','Ratnapura','active','2025-02-14 15:55:00'),
(11,'Amaya Wijesinghe','amaya@example.com','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','librarian','0759990011','Colombo 07','active','2025-02-20 09:30:00'),
(12,'Pasindu Madushanka','pasindu@example.com','$2y$10$QxJwKYHFa/q8BIPvrO0rzuXcyPvaa1/PWG9trTuIrmiGNUIImgjW2','member','0723334455','Gampaha','suspended','2025-03-01 12:00:00');

-- ---------------------------------------------------------------------
--  Table: categories
--  Book genres / categories.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id`  INT(11) NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(100) NOT NULL,
  `description`  VARCHAR(255) DEFAULT NULL,
  `icon`         VARCHAR(60) DEFAULT 'fa-book',
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`category_id`,`name`,`description`,`icon`) VALUES
(1,'Fiction','Novels, short stories and literary fiction','fa-feather'),
(2,'Science & Technology','Computing, engineering and applied sciences','fa-microchip'),
(3,'History','World history, biographies and civilisations','fa-landmark'),
(4,'Business & Economics','Management, finance and entrepreneurship','fa-chart-line'),
(5,'Children','Picture books and young reader titles','fa-child'),
(6,'Self-Development','Productivity, psychology and wellbeing','fa-brain'),
(7,'Mystery & Thriller','Crime, suspense and detective stories','fa-magnifying-glass'),
(8,'Science Fiction','Futuristic, space and speculative fiction','fa-rocket');

-- ---------------------------------------------------------------------
--  Table: books
--  Core catalogue resource (acts as the "product" of the library).
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `book_id`          INT(11) NOT NULL AUTO_INCREMENT,
  `title`            VARCHAR(200) NOT NULL,
  `author`           VARCHAR(150) NOT NULL,
  `isbn`             VARCHAR(20) DEFAULT NULL,
  `category_id`      INT(11) DEFAULT NULL,
  `description`      TEXT DEFAULT NULL,
  `publisher`        VARCHAR(150) DEFAULT NULL,
  `publish_year`     INT(4) DEFAULT NULL,
  `total_copies`     INT(11) NOT NULL DEFAULT 1,
  `available_copies` INT(11) NOT NULL DEFAULT 1,
  `shelf_location`   VARCHAR(50) DEFAULT NULL,
  `cover_image`      VARCHAR(255) DEFAULT NULL,
  `ebook_file`       VARCHAR(255) DEFAULT NULL,
  `rating`           DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  `views`            INT(11) NOT NULL DEFAULT 0,
  `borrow_count`     INT(11) NOT NULL DEFAULT 0,
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`book_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_books_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `books`
(`book_id`,`title`,`author`,`isbn`,`category_id`,`description`,`publisher`,`publish_year`,`total_copies`,`available_copies`,`shelf_location`,`cover_image`,`ebook_file`,`rating`,`views`,`borrow_count`) VALUES
(1,'The Silent Patient','Alex Michaelides','9781250301697',7,'A psychological thriller about a woman who shoots her husband and then stops speaking.','Celadon Books',2019,5,3,'A1-23',NULL,NULL,4.5,320,42),
(2,'Clean Code','Robert C. Martin','9780132350884',2,'A handbook of agile software craftsmanship and best practices for writing maintainable code.','Prentice Hall',2008,8,5,'C2-10',NULL,'sample-clean-code.pdf',4.8,540,88),
(3,'Sapiens: A Brief History of Humankind','Yuval Noah Harari','9780062316097',3,'An exploration of how Homo sapiens came to dominate the planet.','Harper',2015,6,4,'B1-05',NULL,NULL,4.7,610,76),
(4,'Atomic Habits','James Clear','9780735211292',6,'An easy and proven way to build good habits and break bad ones.','Avery',2018,10,6,'D3-02',NULL,'sample-atomic-habits.pdf',4.9,890,134),
(5,'The Pragmatic Programmer','Andrew Hunt, David Thomas','9780201616224',2,'Your journey to mastery in software development.','Addison-Wesley',1999,4,2,'C2-11',NULL,NULL,4.6,410,59),
(6,'Dune','Frank Herbert','9780441013593',8,'A landmark science fiction novel set on the desert planet Arrakis.','Ace Books',1965,7,4,'E1-15',NULL,NULL,4.7,720,98),
(7,'The Lean Startup','Eric Ries','9780307887894',4,'How today''s entrepreneurs use continuous innovation to create radically successful businesses.','Crown Business',2011,5,3,'F2-07',NULL,NULL,4.3,280,47),
(8,'Where the Crawdads Sing','Delia Owens','9780735219090',1,'A coming-of-age mystery set in the marshes of North Carolina.','G.P. Putnam''s Sons',2018,6,5,'A1-08',NULL,NULL,4.6,500,71),
(9,'Educated','Tara Westover','9780399590504',3,'A memoir about a woman who leaves her survivalist family to pursue education.','Random House',2018,4,2,'B1-12',NULL,NULL,4.5,360,55),
(10,'The Midnight Library','Matt Haig','9780525559474',1,'Between life and death there is a library of infinite possibilities.','Viking',2020,9,7,'A1-19',NULL,'sample-midnight-library.pdf',4.4,470,83),
(11,'Thinking, Fast and Slow','Daniel Kahneman','9780374533557',6,'A tour of the two systems that drive the way we think.','Farrar, Straus and Giroux',2011,5,3,'D3-09',NULL,NULL,4.4,390,62),
(12,'The Very Hungry Caterpillar','Eric Carle','9780399226908',5,'A classic picture book following a caterpillar''s transformation.','World Publishing',1969,12,10,'G1-01',NULL,NULL,4.8,210,150),
(13,'Introduction to Algorithms','Cormen, Leiserson, Rivest, Stein','9780262033848',2,'The comprehensive modern textbook on algorithms.','MIT Press',2009,3,1,'C2-15',NULL,NULL,4.7,330,40),
(14,'Gone Girl','Gillian Flynn','9780307588371',7,'A thriller about a marriage gone terribly wrong.','Crown Publishing',2012,6,4,'A2-04',NULL,NULL,4.2,440,68),
(15,'Zero to One','Peter Thiel','9780804139298',4,'Notes on startups, or how to build the future.','Crown Business',2014,5,4,'F2-11',NULL,NULL,4.3,300,51);

-- ---------------------------------------------------------------------
--  Table: features
--  Showcased system features (powers Features.php + admin CRUD).
--  Mirrors the assignment's "Features" table specification.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `features`;
CREATE TABLE `features` (
  `feature_id`   INT(11) NOT NULL AUTO_INCREMENT,
  `feature_name` VARCHAR(120) NOT NULL,
  `description`  TEXT DEFAULT NULL,
  `facilities`   VARCHAR(255) DEFAULT NULL,
  `user`         VARCHAR(120) DEFAULT NULL,
  `image`        VARCHAR(255) DEFAULT NULL,
  `icon`         VARCHAR(60) DEFAULT 'fa-star',
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `features` (`feature_id`,`feature_name`,`description`,`facilities`,`user`,`icon`) VALUES
(1,'Smart Search & Filters','Find any title in milliseconds with live search across title, author, ISBN and category plus advanced filters.','Live search, category filters, availability filter','System Administrator','fa-magnifying-glass'),
(2,'AI Book Recommendations','Personalised suggestions based on your reading history and the most popular titles in your favourite genres.','Personalised picks, trending books, genre matching','System Administrator','fa-wand-magic-sparkles'),
(3,'QR / Barcode Scanning','Every book gets a unique QR code for fast issue and return at the counter using any phone camera.','Generated QR codes, quick checkout','System Administrator','fa-qrcode'),
(4,'Digital Library (eBooks)','Read and download selected titles as PDF/eBooks directly from your dashboard, anytime.','PDF reader, downloads, offline access','Nimali Perera','fa-tablet-screen-button'),
(5,'Reservation Queue','Reserve a borrowed book and join an automatic queue. Get notified the moment it is available.','Auto queue position, ready alerts','Nimali Perera','fa-people-line'),
(6,'Automated Fine Tracking','Overdue fines are calculated automatically every day so members always know what they owe.','Daily calculation, transparent rates','System Administrator','fa-coins'),
(7,'Email / SMS Reminders','Due-date and overdue reminders are pushed to members so books come back on time.','Due reminders, overdue alerts','System Administrator','fa-bell'),
(8,'Reading History & Analytics','Track every book you have borrowed and visualise your reading journey over time.','History timeline, personal stats','Nimali Perera','fa-chart-pie'),
(9,'Real-time Inventory','Available copy counts update instantly on every borrow, return and reservation.','Live availability, no overselling','System Administrator','fa-boxes-stacked'),
(10,'Role-based Dashboards','Dedicated, secure dashboards for administrators, librarians and members.','Admin, librarian & member views','System Administrator','fa-user-shield');

-- ---------------------------------------------------------------------
--  Table: borrowings
--  Book issue/return transactions (the library's core activity).
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `borrowings`;
CREATE TABLE `borrowings` (
  `borrow_id`    INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11) NOT NULL,
  `book_id`      INT(11) NOT NULL,
  `borrow_date`  DATE NOT NULL,
  `due_date`     DATE NOT NULL,
  `return_date`  DATE DEFAULT NULL,
  `status`       ENUM('borrowed','returned','overdue') NOT NULL DEFAULT 'borrowed',
  `fine_amount`  DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`borrow_id`),
  KEY `user_id` (`user_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `fk_borrow_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_borrow_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dates are RELATIVE to the import date (CURDATE) so the demo always looks
-- current: active loans are due in the near future, one is mildly overdue,
-- and returned loans sit in the recent past.
INSERT INTO `borrowings`
(`borrow_id`,`user_id`,`book_id`,`borrow_date`,`due_date`,`return_date`,`status`,`fine_amount`) VALUES
(1,3,2, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 26 DAY), DATE_SUB(CURDATE(), INTERVAL 28 DAY),'returned',0.00),
(2,3,4, DATE_SUB(CURDATE(), INTERVAL 5 DAY),  DATE_ADD(CURDATE(), INTERVAL 9 DAY),  NULL,'borrowed',0.00),
(3,4,1, DATE_SUB(CURDATE(), INTERVAL 35 DAY), DATE_SUB(CURDATE(), INTERVAL 21 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY),'returned',50.00),
(4,5,6, DATE_SUB(CURDATE(), INTERVAL 6 DAY),  DATE_ADD(CURDATE(), INTERVAL 8 DAY),  NULL,'borrowed',0.00),
(5,6,3, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY), DATE_SUB(CURDATE(), INTERVAL 17 DAY),'returned',0.00),
(6,7,10,DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY),  NULL,'overdue',60.00),
(7,8,5, DATE_SUB(CURDATE(), INTERVAL 4 DAY),  DATE_ADD(CURDATE(), INTERVAL 10 DAY), NULL,'borrowed',0.00),
(8,9,8, DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(CURDATE(), INTERVAL 11 DAY), DATE_SUB(CURDATE(), INTERVAL 12 DAY),'returned',0.00),
(9,10,11,DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), NULL,'borrowed',0.00),
(10,4,13,DATE_SUB(CURDATE(), INTERVAL 45 DAY),DATE_SUB(CURDATE(), INTERVAL 31 DAY), DATE_SUB(CURDATE(), INTERVAL 25 DAY),'returned',60.00),
(11,5,7, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), NULL,'borrowed',0.00),
(12,3,14,DATE_SUB(CURDATE(), INTERVAL 32 DAY),DATE_SUB(CURDATE(), INTERVAL 18 DAY), DATE_SUB(CURDATE(), INTERVAL 19 DAY),'returned',0.00);

-- ---------------------------------------------------------------------
--  Table: reservations
--  Reservation queue for borrowed books.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `reservation_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`        INT(11) NOT NULL,
  `book_id`        INT(11) NOT NULL,
  `reserved_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status`         ENUM('pending','ready','fulfilled','cancelled') NOT NULL DEFAULT 'pending',
  `queue_position` INT(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`reservation_id`),
  KEY `user_id` (`user_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `fk_res_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reservations`
(`reservation_id`,`user_id`,`book_id`,`reserved_at`,`status`,`queue_position`) VALUES
(1,6,4, DATE_SUB(NOW(), INTERVAL 4 DAY),'pending',1),
(2,8,4, DATE_SUB(NOW(), INTERVAL 3 DAY),'pending',2),
(3,9,6, DATE_SUB(NOW(), INTERVAL 2 DAY),'pending',1),
(4,3,13,DATE_SUB(NOW(), INTERVAL 6 DAY),'ready',1),
(5,7,2, DATE_SUB(NOW(), INTERVAL 10 DAY),'fulfilled',1),
(6,10,1,DATE_SUB(NOW(), INTERVAL 1 DAY),'pending',1),
(7,4,10,DATE_SUB(NOW(), INTERVAL 1 DAY),'pending',1),
(8,5,14,DATE_SUB(NOW(), INTERVAL 8 DAY),'cancelled',1),
(9,6,11,DATE_SUB(NOW(), INTERVAL 12 HOUR),'pending',1),
(10,8,5,DATE_SUB(NOW(), INTERVAL 6 HOUR),'pending',1);

-- ---------------------------------------------------------------------
--  Table: fines
--  Detailed fine ledger linked to borrowings.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `fines`;
CREATE TABLE `fines` (
  `fine_id`    INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) NOT NULL,
  `borrow_id`  INT(11) DEFAULT NULL,
  `amount`     DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `reason`     VARCHAR(255) DEFAULT NULL,
  `status`     ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`fine_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_fine_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `fines` (`fine_id`,`user_id`,`borrow_id`,`amount`,`reason`,`status`) VALUES
(1,4,3,50.00,'Returned 5 days late (LKR 10/day)','paid'),
(2,7,6,60.00,'Overdue book','unpaid'),
(3,4,10,60.00,'Returned 6 days late','paid'),
(4,12,NULL,250.00,'Lost book replacement charge','unpaid');

-- ---------------------------------------------------------------------
--  Table: reviews
--  Member book reviews and ratings.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `review_id`  INT(11) NOT NULL AUTO_INCREMENT,
  `book_id`    INT(11) NOT NULL,
  `user_id`    INT(11) NOT NULL,
  `rating`     TINYINT(1) NOT NULL DEFAULT 5,
  `comment`    TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `book_id` (`book_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_review_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reviews` (`review_id`,`book_id`,`user_id`,`rating`,`comment`) VALUES
(1,2,3,5,'Essential reading for every developer. Changed how I write code.'),
(2,4,4,5,'Practical, actionable and genuinely life-changing.'),
(3,6,5,4,'Epic world-building but a slow start. Worth it.'),
(4,1,6,5,'Could not put it down. The twist is incredible!'),
(5,3,7,5,'A sweeping, thought-provoking history of our species.'),
(6,10,8,4,'A comforting and imaginative read.'),
(7,13,4,5,'The definitive algorithms reference. Dense but invaluable.');

-- ---------------------------------------------------------------------
--  Table: notifications
--  In-app notifications for reminders, alerts and queue updates.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `notification_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`         INT(11) NOT NULL,
  `title`           VARCHAR(150) NOT NULL,
  `message`         VARCHAR(255) NOT NULL,
  `type`            ENUM('info','due','overdue','reservation','fine','system') NOT NULL DEFAULT 'info',
  `is_read`         TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notifications` (`notification_id`,`user_id`,`title`,`message`,`type`,`is_read`) VALUES
(1,3,'Book due soon','"Atomic Habits" is due in about a week. Please return on time.','due',0),
(2,7,'Overdue book','"The Midnight Library" is overdue. A fine of LKR 60 has been applied.','overdue',0),
(3,3,'Reservation ready','Your reserved book "Introduction to Algorithms" is ready for pickup.','reservation',1),
(4,4,'Welcome to SmartShelf','Thanks for joining! Browse the catalogue and borrow your first book.','system',1),
(5,5,'Book due soon','"Dune" is due soon. Don''t forget to return it.','due',0);

-- ---------------------------------------------------------------------
--  Table: contact_messages
--  Messages submitted through the public Contact-Us form.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `message_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `subject`    VARCHAR(200) DEFAULT NULL,
  `message`    TEXT NOT NULL,
  `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `contact_messages` (`name`,`email`,`subject`,`message`,`is_read`) VALUES
('Saman Kumara','saman@example.com','Membership query','How do I upgrade my membership to borrow more books?',0),
('Nadeesha Perera','nadeesha@example.com','Lost book','I think I lost a borrowed book. What is the procedure?',1);

-- ---------------------------------------------------------------------
--  Table: activity_log
--  Audit trail of important actions for the admin dashboard.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `log_id`     INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) DEFAULT NULL,
  `action`     VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `activity_log` (`user_id`,`action`,`created_at`) VALUES
(1,'System initialised and seed data loaded','2025-01-05 09:05:00'),
(2,'Issued "Clean Code" to Kasun Silva','2025-05-01 09:30:00'),
(2,'Registered new book "Zero to One"','2025-05-03 11:00:00'),
(3,'Borrowed "Atomic Habits"','2025-05-20 10:00:00'),
(2,'Marked "The Midnight Library" as overdue','2025-06-01 08:00:00');

SET FOREIGN_KEY_CHECKS = 1;
-- =====================================================================
--  End of backup
-- =====================================================================
