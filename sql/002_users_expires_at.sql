-- TolCare — demo accounts can expire on a date.
-- Run after sql/001_users_table.sql (import it in phpMyAdmin).
-- NULL means the account never expires (normal admin accounts).
-- A demo account gets a date, e.g.:
--   UPDATE users SET expires_at = '2027-02-06' WHERE username = 'demo';
ALTER TABLE `users`
ADD COLUMN `expires_at` DATE NULL DEFAULT NULL;
