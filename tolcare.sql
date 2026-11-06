-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2026 at 03:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tolcare`
--

-- --------------------------------------------------------

--
-- Table structure for table `agendas`
--

CREATE TABLE `agendas` (
  `id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','published','closed') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agendas`
--

INSERT INTO `agendas` (`id`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(25, '2026-03-14', '2026-04-10', 'draft', '2026-03-10 13:15:18');

-- --------------------------------------------------------

--
-- Table structure for table `agenda_shifts`
--

CREATE TABLE `agenda_shifts` (
  `id` int(11) NOT NULL,
  `agenda_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `shift_type` enum('day','night') NOT NULL,
  `emp_id` int(11) NOT NULL,
  `team` tinyint(4) NOT NULL,
  `position_id` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `type` enum('SingleDate','DateRange','SpecificHours') DEFAULT NULL,
  `single_date` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `specific_day` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`id`, `emp_id`, `type`, `single_date`, `start_date`, `end_date`, `specific_day`, `start_time`, `end_time`, `created_at`) VALUES
(6, 1, 'SingleDate', '2026-03-18', NULL, NULL, NULL, NULL, NULL, '2026-03-10 12:13:20'),
(7, 1, 'DateRange', NULL, '2026-03-23', '2026-03-24', NULL, NULL, NULL, '2026-03-10 12:48:39'),
(8, 1, 'SpecificHours', NULL, NULL, NULL, '2026-03-15', '08:00:00', '15:00:00', '2026-03-10 13:02:18');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `emp_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `position_id` int(11) DEFAULT NULL,
  `shift` enum('Day','Night') DEFAULT NULL,
  `team` enum('1','2') DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `extra1` varchar(255) DEFAULT NULL,
  `extra2` varchar(255) DEFAULT NULL,
  `extra3` varchar(255) DEFAULT NULL,
  `extra4` varchar(255) DEFAULT NULL,
  `extra5` varchar(255) DEFAULT NULL,
  `extra6` varchar(255) DEFAULT NULL,
  `extra7` varchar(255) DEFAULT NULL,
  `extra8` varchar(255) DEFAULT NULL,
  `extra9` varchar(255) DEFAULT NULL,
  `extra10` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`emp_id`, `first_name`, `last_name`, `position_id`, `shift`, `team`, `type`, `status`, `extra1`, `extra2`, `extra3`, `extra4`, `extra5`, `extra6`, `extra7`, `extra8`, `extra9`, `extra10`, `attachment`, `created_at`) VALUES
(1, 'Carlos', 'Lopez', 3, 'Day', '1', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(2, 'Miguel', 'Ramirez', 3, 'Day', '2', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(3, 'Juan', 'Perez', 1, 'Day', '1', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(4, 'Luis', 'Garcia', 1, 'Day', '2', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(5, 'Pedro', 'Martinez', 1, 'Night', '1', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(6, 'Jorge', 'Hernandez', 1, 'Night', '2', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(7, 'Andres', 'Torres', 2, 'Day', '1', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(8, 'Roberto', 'Flores', 2, 'Day', '1', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(9, 'Daniel', 'Vargas', 2, 'Day', '1', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(10, 'Sergio', 'Castillo', 2, 'Night', '1', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(11, 'Fernando', 'Rios', 2, 'Night', '1', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(12, 'Ricardo', 'Mendoza', 2, 'Night', '1', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(13, 'Alberto', 'Silva', 2, 'Day', '2', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(14, 'Manuel', 'Cruz', 2, 'Day', '2', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(15, 'Rafael', 'Morales', 2, 'Day', '2', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(16, 'Eduardo', 'Ortega', 2, 'Night', '2', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(17, 'Victor', 'Navarro', 2, 'Night', '2', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33'),
(18, 'Diego', 'Santos', 2, 'Night', '2', NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24 19:07:33');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `log_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `position_name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `position_name`, `description`) VALUES
(1, 'Personal Medications', 'Direct resident care'),
(2, 'Residence Attendant', 'Support and supervision'),
(3, 'Cook', 'Cook');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_settings`
--

CREATE TABLE `schedule_settings` (
  `id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `starting_team` enum('1','2') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staffing_parameters`
--

CREATE TABLE `staffing_parameters` (
  `id` int(11) NOT NULL,
  `team` enum('1','2') NOT NULL,
  `shift` enum('Day','Night') NOT NULL,
  `position_id` int(11) NOT NULL,
  `required_qty` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffing_parameters`
--

INSERT INTO `staffing_parameters` (`id`, `team`, `shift`, `position_id`, `required_qty`, `created_at`) VALUES
(20, '1', 'Day', 3, 1, '2026-02-23 16:31:43'),
(21, '1', 'Day', 1, 1, '2026-02-23 16:31:43'),
(22, '1', 'Day', 2, 3, '2026-02-23 16:31:43'),
(23, '1', 'Night', 1, 1, '2026-02-24 23:08:18'),
(24, '1', 'Night', 2, 3, '2026-02-24 23:08:18'),
(25, '2', 'Day', 3, 1, '2026-02-24 23:08:37'),
(26, '2', 'Day', 1, 1, '2026-02-24 23:08:37'),
(27, '2', 'Day', 2, 3, '2026-02-24 23:08:37'),
(28, '2', 'Night', 1, 1, '2026-02-24 23:08:54'),
(29, '2', 'Night', 2, 3, '2026-02-24 23:08:54');

-- --------------------------------------------------------

--
-- Table structure for table `team_rotation_pattern`
--

CREATE TABLE `team_rotation_pattern` (
  `id` int(11) NOT NULL,
  `team` enum('1','2') NOT NULL,
  `week_number` enum('1','2') NOT NULL,
  `saturday` tinyint(1) DEFAULT 0,
  `sunday` tinyint(1) DEFAULT 0,
  `monday` tinyint(1) DEFAULT 0,
  `tuesday` tinyint(1) DEFAULT 0,
  `wednesday` tinyint(1) DEFAULT 0,
  `thursday` tinyint(1) DEFAULT 0,
  `friday` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_rotation_pattern`
--

INSERT INTO `team_rotation_pattern` (`id`, `team`, `week_number`, `saturday`, `sunday`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`) VALUES
(25, '1', '1', 1, 1, 0, 0, 1, 1, 0),
(26, '1', '2', 0, 0, 1, 1, 0, 0, 1),
(27, '2', '1', 0, 0, 1, 1, 0, 0, 1),
(28, '2', '2', 1, 1, 0, 0, 1, 1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agendas`
--
ALTER TABLE `agendas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_range` (`start_date`,`end_date`);

--
-- Indexes for table `agenda_shifts`
--
ALTER TABLE `agenda_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agenda_id` (`agenda_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_employee` (`emp_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`emp_id`),
  ADD KEY `fk_position` (`position_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule_settings`
--
ALTER TABLE `schedule_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staffing_parameters`
--
ALTER TABLE `staffing_parameters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_shift_position` (`team`,`shift`,`position_id`);

--
-- Indexes for table `team_rotation_pattern`
--
ALTER TABLE `team_rotation_pattern`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agendas`
--
ALTER TABLE `agendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `agenda_shifts`
--
ALTER TABLE `agenda_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=666;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedule_settings`
--
ALTER TABLE `schedule_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `staffing_parameters`
--
ALTER TABLE `staffing_parameters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `team_rotation_pattern`
--
ALTER TABLE `team_rotation_pattern`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agenda_shifts`
--
ALTER TABLE `agenda_shifts`
  ADD CONSTRAINT `agenda_shifts_ibfk_1` FOREIGN KEY (`agenda_id`) REFERENCES `agendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agenda_shifts_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agenda_shifts_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`);

--
-- Constraints for table `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `fk_employee` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
