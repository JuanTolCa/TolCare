-- TolCare — users table for login (added Nov 2026)
-- Import this file in phpMyAdmin (database: tolcare) BEFORE using login.php.
-- No default user is included on purpose: the first time you open login.php
-- with an empty users table, it shows a form to create your admin account.

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
