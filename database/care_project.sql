-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2026 at 08:42 AM
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
-- Database: `care_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `full_name`) VALUES
(1, 1, 'Super Admin'),
(2, 1, 'System Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `problem` text DEFAULT NULL,
  `status` enum('pending','approved','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `doctor_id`, `patient_id`, `appointment_date`, `appointment_time`, `problem`, `status`, `created_at`) VALUES
(4, 7, 4, '2026-03-12', '09:00:00', 'I book an appointment to discuss my health concerns with a doctor, receive medical guidance, and get the necessary treatment.', 'cancelled', '2026-03-10 16:37:08'),
(5, 8, 4, '2026-03-13', '09:00:00', 'I book an appointment to discuss my health concerns with a doctor, receive medical guidance, and get the necessary treatment.', 'completed', '2026-03-10 16:41:01'),
(6, 2, 4, '2026-03-10', '09:00:00', 'I would like to schedule this appointment to discuss my current health condition. The issue has been affecting my daily routine. I want to receive medical advice from the doctor.', 'pending', '2026-03-10 16:42:33'),
(7, 9, 4, '2026-03-11', '09:00:00', 'I want to meet the doctor to talk about my health concerns. Some symptoms have been troubling me recently. I hope to get the right diagnosis and treatment.', 'approved', '2026-03-10 16:46:22'),
(8, 7, 5, '2026-03-10', '09:00:00', 'I am scheduling this appointment to seek professional medical help. I want to understand the cause of my symptoms. The doctor’s advice will help me manage my health better.', 'pending', '2026-03-10 17:32:37'),
(9, 8, 5, '2026-03-10', '09:00:00', 'I am scheduling this appointment to seek professional medical help. I want to understand the cause of my symptoms. The doctor’s advice will help me manage my health better.', 'pending', '2026-03-10 17:34:15'),
(10, 2, 5, '2026-03-14', '09:00:00', 'I am scheduling this appointment to seek professional medical help. I want to understand the cause of my symptoms. The doctor’s advice will help me manage my health better.', 'cancelled', '2026-03-10 17:34:37'),
(11, 9, 5, '2026-03-13', '09:00:00', 'I am scheduling this appointment to seek professional medical help. I want to understand the cause of my symptoms. The doctor’s advice will help me manage my health better.', 'pending', '2026-03-10 17:34:57'),
(12, 7, 11, '2026-03-11', '09:00:00', 'I am scheduling this appointment to seek professional medical help. I want to understand the cause of my symptoms. The doctor’s advice will help me manage my health better.', 'pending', '2026-03-10 17:42:06'),
(13, 8, 11, '2026-03-11', '09:00:00', 'I am scheduling this appointment to seek professional medical help. I want to understand the cause of my symptoms. The doctor’s advice will help me manage my health better.', 'pending', '2026-03-10 17:43:39'),
(14, 2, 11, '2026-03-16', '09:00:00', 'I am scheduling this appointment to seek professional medical help. I want to understand the cause of my symptoms. The doctor’s advice will help me manage my health better.', 'approved', '2026-03-10 17:43:53'),
(15, 9, 11, '2026-03-14', '09:00:00', 'I am scheduling this appointment to seek professional medical help. I want to understand the cause of my symptoms. The doctor’s advice will help me manage my health better.', 'pending', '2026-03-10 17:44:11'),
(16, 7, 6, '2026-03-14', '09:00:00', 'I booked an appointment with Dr. Ahmed to discuss my ongoing stomach and digestive issues.\r\nAs a specialist in Gastroenterology, he can properly diagnose and guide me with the right treatment.\r\nThis consultation will help me understand my condition and improve my digestive health.', 'pending', '2026-03-11 08:53:03'),
(17, 8, 6, '2026-03-15', '09:00:00', 'I booked an appointment with Dr. Ahsan to check and manage my blood sugar levels.\r\nAs an expert in Diabetes, he can guide me with proper treatment and lifestyle advice.\r\nThis consultation will help me control my condition and stay healthier.', 'pending', '2026-03-11 08:53:46'),
(18, 2, 6, '2026-03-12', '09:00:00', 'I booked an appointment with Dr. Asfa to check my heart health and discuss my symptoms.\r\nAs a specialist in Cardiology, she can provide proper diagnosis and treatment.\r\nThis consultation will help me better understand and take care of my heart. ❤️', 'approved', '2026-03-11 08:54:39'),
(19, 9, 6, '2026-03-15', '09:00:00', 'I booked an appointment with Dr. Ayyan to discuss my kidney health and related symptoms.\r\nAs a specialist in Nephrology, he can properly diagnose and guide me with the right treatment.\r\nThis consultation will help me maintain better kidney function and overall health. 💧', 'pending', '2026-03-11 08:55:48'),
(20, 7, 7, '2026-03-15', '09:00:00', 'I booked an appointment with Dr. Ahmed to discuss my ongoing stomach and digestive issues.\r\nAs a specialist in Gastroenterology, he can properly diagnose and guide me with the right treatment.\r\nThis consultation will help me understand my condition and improve my digestive health.', 'pending', '2026-03-11 08:56:48'),
(21, 8, 7, '2026-03-14', '09:00:00', 'I booked an appointment with Dr. Ahsan to check and manage my blood sugar levels.\r\nAs an expert in Diabetes, he can guide me with proper treatment and lifestyle advice.\r\nThis consultation will help me control my condition and stay healthier.', 'pending', '2026-03-11 08:57:09'),
(22, 2, 7, '2026-03-15', '09:00:00', 'I booked an appointment with Dr. Asfa to check my heart health and discuss my symptoms.\r\nAs a specialist in Cardiology, she can provide proper diagnosis and treatment.\r\nThis consultation will help me better understand and take care of my heart. ❤️', 'completed', '2026-03-11 08:57:25'),
(23, 9, 7, '2026-03-16', '09:00:00', 'I booked an appointment with Dr. Ayyan to discuss my kidney health and related symptoms.\r\nAs a specialist in Nephrology, he can properly diagnose and guide me with the right treatment.\r\nThis consultation will help me maintain better kidney function and overall health. 💧', 'pending', '2026-03-11 08:57:52'),
(24, 7, 8, '2026-03-13', '09:00:00', 'I booked an appointment with Dr. Ahmed to discuss my ongoing stomach and digestive issues.\r\nAs a specialist in Gastroenterology, he can properly diagnose and guide me with the right treatment.\r\nThis consultation will help me understand my condition and improve my digestive health.', 'pending', '2026-03-11 08:58:28'),
(25, 8, 8, '2026-03-12', '09:00:00', 'I booked an appointment with Dr. Ahsan to check and manage my blood sugar levels.\r\nAs an expert in Diabetes, he can guide me with proper treatment and lifestyle advice.\r\nThis consultation will help me control my condition and stay healthier.', 'pending', '2026-03-11 08:58:44'),
(26, 2, 8, '2026-03-11', '09:00:00', 'I booked an appointment with Dr. Asfa to check my heart health and discuss my symptoms.\r\nAs a specialist in Cardiology, she can provide proper diagnosis and treatment.\r\nThis consultation will help me better understand and take care of my heart. ❤️', 'completed', '2026-03-11 08:59:05'),
(27, 9, 8, '2026-03-12', '09:00:00', 'I booked an appointment with Dr. Ayyan to discuss my kidney health and related symptoms.\r\nAs a specialist in Nephrology, he can properly diagnose and guide me with the right treatment.\r\nThis consultation will help me maintain better kidney function and overall health. 💧', 'pending', '2026-03-11 08:59:20'),
(28, 7, 9, '2026-03-16', '09:00:00', 'I booked an appointment with Dr. Ahmed to discuss my ongoing stomach and digestive issues.\r\nAs a specialist in Gastroenterology, he can properly diagnose and guide me with the right treatment.\r\nThis consultation will help me understand my condition and improve my digestive health.', 'pending', '2026-03-11 09:01:17'),
(29, 8, 9, '2026-03-16', '09:00:00', 'I booked an appointment with Dr. Ahsan to check and manage my blood sugar levels.\r\nAs an expert in Diabetes, he can guide me with proper treatment and lifestyle advice.\r\nThis consultation will help me control my condition and stay healthier.', 'pending', '2026-03-11 09:01:30'),
(30, 2, 9, '2026-03-13', '09:00:00', 'I booked an appointment with Dr. Asfa to check my heart health and discuss my symptoms.\r\nAs a specialist in Cardiology, she can provide proper diagnosis and treatment.\r\nThis consultation will help me better understand and take care of my heart. ❤️', 'approved', '2026-03-11 09:01:44'),
(31, 2, 14, '2026-03-11', '09:00:00', 'I booked an appointment with Dr. Asfa to check my heart health and discuss my symptoms.\r\nAs a specialist in Cardiology, she can provide proper diagnosis and treatment.\r\nThis consultation will help me better understand and take care of my heart. ❤️', 'approved', '2026-03-11 09:12:23');

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('available','booked') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`id`, `doctor_id`, `available_date`, `start_time`, `end_time`, `status`) VALUES
(9, 2, '2026-03-10', '09:00:00', '17:00:00', 'booked'),
(10, 2, '2026-03-11', '09:00:00', '17:00:00', 'booked'),
(11, 2, '2026-03-12', '09:00:00', '17:00:00', 'booked'),
(12, 2, '2026-03-13', '09:00:00', '17:00:00', 'booked'),
(13, 2, '2026-03-14', '09:00:00', '17:00:00', 'booked'),
(14, 2, '2026-03-15', '09:00:00', '17:00:00', 'booked'),
(15, 2, '2026-03-16', '09:00:00', '17:00:00', 'booked'),
(16, 11, '2026-03-10', '09:00:00', '17:00:00', 'available'),
(17, 11, '2026-03-11', '09:00:00', '17:00:00', 'available'),
(18, 11, '2026-03-12', '09:00:00', '17:00:00', 'available'),
(19, 11, '2026-03-13', '09:00:00', '17:00:00', 'available'),
(20, 11, '2026-03-14', '09:00:00', '17:00:00', 'available'),
(21, 11, '2026-03-15', '09:00:00', '17:00:00', 'available'),
(22, 11, '2026-03-16', '09:00:00', '17:00:00', 'available'),
(23, 10, '2026-03-10', '09:00:00', '17:00:00', 'available'),
(24, 10, '2026-03-11', '09:00:00', '17:00:00', 'available'),
(25, 10, '2026-03-12', '09:00:00', '17:00:00', 'available'),
(26, 10, '2026-03-13', '09:00:00', '17:00:00', 'available'),
(27, 10, '2026-03-14', '09:00:00', '17:00:00', 'available'),
(28, 10, '2026-03-15', '09:00:00', '17:00:00', 'available'),
(29, 10, '2026-03-16', '09:00:00', '17:00:00', 'available'),
(30, 9, '2026-03-10', '09:00:00', '17:00:00', 'available'),
(31, 9, '2026-03-11', '09:00:00', '17:00:00', 'booked'),
(32, 9, '2026-03-12', '09:00:00', '17:00:00', 'booked'),
(33, 9, '2026-03-13', '09:00:00', '17:00:00', 'booked'),
(34, 9, '2026-03-14', '09:00:00', '17:00:00', 'booked'),
(35, 9, '2026-03-15', '09:00:00', '17:00:00', 'booked'),
(36, 9, '2026-03-16', '09:00:00', '17:00:00', 'booked'),
(37, 8, '2026-03-10', '09:00:00', '17:00:00', 'booked'),
(38, 8, '2026-03-11', '09:00:00', '17:00:00', 'booked'),
(39, 8, '2026-03-12', '09:00:00', '17:00:00', 'booked'),
(40, 8, '2026-03-13', '09:00:00', '17:00:00', 'booked'),
(41, 8, '2026-03-14', '09:00:00', '17:00:00', 'booked'),
(42, 8, '2026-03-15', '09:00:00', '17:00:00', 'booked'),
(43, 8, '2026-03-16', '09:00:00', '17:00:00', 'booked'),
(44, 7, '2026-03-10', '09:00:00', '17:00:00', 'booked'),
(45, 7, '2026-03-11', '09:00:00', '17:00:00', 'booked'),
(46, 7, '2026-03-12', '09:00:00', '17:00:00', 'booked'),
(47, 7, '2026-03-13', '09:00:00', '17:00:00', 'booked'),
(48, 7, '2026-03-14', '09:00:00', '17:00:00', 'booked'),
(49, 7, '2026-03-15', '09:00:00', '17:00:00', 'booked'),
(50, 7, '2026-03-16', '09:00:00', '17:00:00', 'booked'),
(52, 6, '2026-03-10', '09:00:00', '17:00:00', 'available'),
(53, 6, '2026-03-11', '09:00:00', '17:00:00', 'available'),
(54, 6, '2026-03-12', '09:00:00', '17:00:00', 'available'),
(55, 6, '2026-03-13', '09:00:00', '17:00:00', 'available'),
(56, 6, '2026-03-14', '09:00:00', '17:00:00', 'available'),
(57, 6, '2026-03-15', '09:00:00', '17:00:00', 'available'),
(58, 6, '2026-03-16', '09:00:00', '17:00:00', 'available'),
(59, 5, '2026-03-10', '09:00:00', '17:00:00', 'available'),
(60, 5, '2026-03-11', '09:00:00', '17:00:00', 'available'),
(61, 5, '2026-03-12', '09:00:00', '17:00:00', 'available'),
(62, 5, '2026-03-13', '09:00:00', '17:00:00', 'available'),
(63, 5, '2026-03-14', '09:00:00', '17:00:00', 'available'),
(64, 5, '2026-03-15', '09:00:00', '17:00:00', 'available'),
(65, 5, '2026-03-16', '09:00:00', '17:00:00', 'available'),
(66, 4, '2026-03-10', '09:00:00', '17:00:00', 'available'),
(67, 4, '2026-03-11', '09:00:00', '17:00:00', 'available'),
(68, 4, '2026-03-12', '09:00:00', '17:00:00', 'available'),
(69, 4, '2026-03-13', '09:00:00', '17:00:00', 'available'),
(70, 4, '2026-03-14', '09:00:00', '17:00:00', 'available'),
(71, 4, '2026-03-15', '09:00:00', '17:00:00', 'available'),
(72, 4, '2026-03-16', '09:00:00', '17:00:00', 'available'),
(73, 3, '2026-03-10', '09:00:00', '17:00:00', 'available'),
(74, 3, '2026-03-11', '09:00:00', '17:00:00', 'available'),
(75, 3, '2026-03-12', '09:00:00', '17:00:00', 'available'),
(76, 3, '2026-03-13', '09:00:00', '17:00:00', 'available'),
(77, 3, '2026-03-14', '09:00:00', '17:00:00', 'available'),
(78, 3, '2026-03-15', '09:00:00', '17:00:00', 'available'),
(79, 3, '2026-03-16', '09:00:00', '17:00:00', 'available'),
(80, 2, '2026-03-11', '09:00:00', '17:00:00', 'booked'),
(81, 2, '2026-03-12', '09:00:00', '17:00:00', 'available'),
(82, 2, '2026-03-14', '09:00:00', '17:00:00', 'available'),
(83, 2, '2026-03-17', '09:00:00', '17:00:00', 'available'),
(84, 2, '2026-03-17', '09:00:00', '17:00:00', 'available'),
(85, 2, '2026-03-18', '09:00:00', '17:00:00', 'available'),
(86, 2, '2026-03-19', '09:00:00', '17:00:00', 'available'),
(87, 2, '2026-03-20', '09:00:00', '17:00:00', 'available'),
(88, 2, '2026-03-21', '09:00:00', '17:00:00', 'available'),
(89, 2, '2026-03-22', '09:00:00', '17:00:00', 'available'),
(90, 2, '2026-03-23', '09:00:00', '17:00:00', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `city_name`, `status`, `created_at`) VALUES
(1, 'Karachi', 'active', '2026-03-05 16:49:10'),
(2, 'Lahore', 'active', '2026-03-05 16:49:10'),
(3, 'Islamabad', 'active', '2026-03-05 16:49:10'),
(4, 'Rawalpindi', 'active', '2026-03-05 16:49:10'),
(5, 'Peshawar', 'active', '2026-03-05 16:49:10'),
(6, 'Quetta', 'active', '2026-03-05 16:49:10'),
(7, 'Multan', 'active', '2026-03-05 16:49:10'),
(8, 'Faisalabad', 'active', '2026-03-05 16:49:10'),
(9, 'Hydrabad', 'active', '2026-03-05 18:12:05'),
(10, 'Sialkot', 'active', '2026-03-05 18:12:21'),
(11, 'Sargodha', 'active', '2026-03-17 07:34:21');

-- --------------------------------------------------------

--
-- Table structure for table `cures`
--

CREATE TABLE `cures` (
  `id` int(11) NOT NULL,
  `disease_id` int(11) NOT NULL,
  `cure_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cures`
--

INSERT INTO `cures` (`id`, `disease_id`, `cure_text`) VALUES
(1, 1, 'Insulin therapy for Type 1 diabetes'),
(2, 1, 'Oral medications such as Metformin'),
(3, 2, 'Antihypertensive medications prescribed by doctor'),
(4, 2, 'Lifestyle modifications and dietary changes'),
(9, 4, 'Medications (statins, beta-blockers)'),
(10, 4, 'Angioplasty or coronary bypass surgery'),
(11, 5, 'Oral diabetes medications or insulin therapy'),
(12, 5, 'Lifestyle modification (diet & physical activity)'),
(13, 6, 'Acid-reducing medications (PPIs)'),
(14, 6, 'Surgical procedure (fundoplication)'),
(15, 3, 'Antiviral medications as prescribed'),
(16, 3, 'Rest and adequate hydration');

-- --------------------------------------------------------

--
-- Table structure for table `diseases`
--

CREATE TABLE `diseases` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diseases`
--

INSERT INTO `diseases` (`id`, `title`, `description`, `created_at`) VALUES
(1, 'Diabetes', 'A chronic disease that occurs either when the pancreas does not produce enough insulin or when the body cannot effectively use the insulin it produces.', '2026-03-05 16:49:53'),
(2, 'Hypertension', 'A long-term medical condition in which the blood pressure in the arteries is persistently elevated.', '2026-03-05 16:49:53'),
(3, 'COVID-19', 'An infectious disease caused by the SARS-CoV-2 virus, primarily affecting the respiratory system.', '2026-03-05 16:49:53'),
(4, 'Cardiologists', 'Narrowing of heart arteries due to plaque buildup, reducing blood flow to the heart.', '2026-03-09 04:27:26'),
(5, 'Endocrinologists', 'Chronic disease where the body cannot properly regulate blood sugar.', '2026-03-09 04:34:58'),
(6, 'Gastroenterologists', 'Acid from the stomach frequently flows back into the esophagus causing irritation.', '2026-03-09 04:37:10');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `qualification` varchar(150) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city_id` int(11) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `full_name`, `specialization`, `qualification`, `experience`, `phone`, `address`, `city_id`, `profile_image`, `created_at`) VALUES
(2, 7, 'Dr. Asfa', 'Cardiologists', 'MBBS', 5, '03001234567', 'Faisalabd', 8, '2d4350c91adcdf76402f.jpg', '2026-03-07 21:52:16'),
(3, 12, 'Dr. Raiba', 'Psychologists', 'MD', 2, '03331234567', '', 1, 'e260a3e1cf9ed8807c6c.jpg', '2026-03-07 22:56:20'),
(4, 13, 'Dr. Kiran', 'Pulmonologist', 'MBBS, FCPS', 5, '03192122871', '', 3, '1280a11712a9712c5866.jpg', '2026-03-09 03:24:01'),
(5, 14, 'Dr. Nimra', 'Dermatologists', 'FCPS', 8, '03123672871', '', 1, '6efe08afe8b913ea0a99.jpg', '2026-03-09 03:26:21'),
(6, 15, 'Dr. Sidra', 'Hematologists', 'MBBS', 11, '03451278361', '', 2, '0b685b74bcd38de3e820.jpg', '2026-03-09 03:28:13'),
(7, 16, 'Dr. Ahmed', 'Gastroenterologists', 'MBBS', 6, '03217628912', 'Multan', 7, '979f07780cb625303944.jpg', '2026-03-09 03:50:28'),
(8, 17, 'Dr. Ahsan', 'Diabetes', 'MBBS, FCPS', 4, '03317629816', 'Peshawar', 5, '9a8657acd383903376fe.jpg', '2026-03-09 03:52:16'),
(9, 18, 'Dr. Ayyan', 'Nephrologists', 'MBBS', 5, '03286287190', 'Quetta', 6, 'f4e7f41c4d60e8c6d06b.jpg', '2026-03-09 03:53:46'),
(10, 19, 'Dr. Fahad', 'Rheumatologists', 'FCPS', 9, '03008719347', 'Rawalpindi', 4, '1bd268c7d2498fb45149.jpg', '2026-03-09 03:55:55'),
(11, 20, 'Dr. Saad', 'Geriatricians', 'FCPS', 12, '03452673901', 'Sialkot', 10, 'd9d21716d6167280b1b7.jpg', '2026-03-09 03:57:20');

-- --------------------------------------------------------

--
-- Table structure for table `medical_news`
--

CREATE TABLE `medical_news` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `published_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_news`
--

INSERT INTO `medical_news` (`id`, `title`, `content`, `image`, `published_date`, `created_at`) VALUES
(1, 'New Breakthrough in Cancer Research', 'Scientists have discovered a new method to target cancer cells more effectively using immunotherapy techniques...', '9b69168247a069890cd0.jpg', '2026-03-01', '2026-03-05 16:49:53'),
(2, 'WHO Updates COVID-19 Guidelines', 'The World Health Organization has released updated guidelines for managing COVID-19 in 2026...', 'e9afb5558e2314887dfc.jpg', '2026-02-28', '2026-03-05 16:49:53'),
(3, 'Heart Disease Prevention Tips', 'Cardiologists recommend these key lifestyle changes to significantly reduce the risk of heart disease...', 'd2153f33d8a75dad2425.jpg', '2026-02-25', '2026-03-05 16:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city_id` int(11) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `full_name`, `phone`, `address`, `city_id`, `profile_image`, `date_of_birth`, `gender`, `created_at`) VALUES
(4, 21, 'M. Affan', '03227389128', 'Faislabad', 8, 'b0892b19c184ca3892ad.jpg', '2003-01-30', 'male', '2026-03-09 04:06:25'),
(5, 22, 'Areeba', '03117829017', 'Islamabad', 3, '4d4e9a0acebc69f629d9.jpg', '2005-07-13', 'female', '2026-03-09 04:17:37'),
(6, 23, 'Sarim Ali', '03008437659', 'Peshawar', 5, '539f7de3e0c871fa8704.jpg', '2001-10-24', 'male', '2026-03-09 04:19:35'),
(7, 24, 'Amna Saud', '03446590274', 'Sialkot', 10, '83a68f5e102fbd0d5030.png', '1999-05-15', 'female', '2026-03-09 04:21:37'),
(8, 25, 'Afshan Malick', '03116728167', 'Lahore', 2, 'c44e469df3bd30197b63.png', '2001-07-25', 'female', '2026-03-10 15:13:22'),
(9, 26, 'Aiman Siddiqui', '03003021837', 'Quetta', 6, 'd351a124fcd0b269581c.jpg', '2003-09-14', 'female', '2026-03-10 15:15:02'),
(10, 27, 'Azlan Ahmed', '03406257183', 'Peshawar', 5, 'ad99cdcd5469a04beec0.jpg', '2002-11-20', 'male', '2026-03-10 15:16:43'),
(11, 28, 'Hamza Muqtaddir', '03217892346', 'Hydrabad', 9, '255b97087c959be440f6.jpg', '2006-12-28', 'male', '2026-03-10 15:18:28'),
(12, 29, 'Kashaf', '03448738901', 'Islamabad', 3, 'df75c323912a5c16bc63.jpg', '2006-04-13', 'female', '2026-03-10 15:19:48'),
(13, 30, 'Umar Rashid', '03321872910', 'Faislabad', 8, '5502bf0bbacabd5eb205.jpg', '2002-10-24', 'male', '2026-03-10 15:22:30'),
(14, 31, 'M. Hamza', '03001234567', 'Orangi Town, Karaci', 1, 'fec3393bd9041813a8a0.jpg', '2007-04-19', 'male', '2026-03-11 09:11:45');

-- --------------------------------------------------------

--
-- Table structure for table `preventions`
--

CREATE TABLE `preventions` (
  `id` int(11) NOT NULL,
  `disease_id` int(11) NOT NULL,
  `prevention_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `preventions`
--

INSERT INTO `preventions` (`id`, `disease_id`, `prevention_text`) VALUES
(1, 1, 'Maintain a healthy weight'),
(2, 1, 'Exercise regularly (at least 30 minutes per day)'),
(3, 1, 'Eat a balanced, low-sugar diet'),
(4, 2, 'Reduce salt intake in diet'),
(5, 2, 'Exercise regularly'),
(6, 2, 'Avoid smoking and excessive alcohol'),
(13, 4, 'Maintain healthy diet (low fat, low salt)'),
(14, 4, 'Regular physical exercise'),
(15, 4, 'Avoid smoking'),
(16, 5, 'Balanced diet with low sugar'),
(17, 5, 'Regular exercise'),
(18, 5, 'Maintain healthy body weight'),
(19, 6, 'Avoid spicy and fatty foods'),
(20, 6, 'Eat smaller meals'),
(21, 6, 'Maintain healthy body weight'),
(22, 3, 'Wear a mask in crowded places'),
(23, 3, 'Wash hands frequently'),
(24, 3, 'Get vaccinated');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','patient') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`, `reset_token`, `token_expiry`) VALUES
(1, 'admin', 'admin@care.com', '$2y$10$3njzlXrorSq55se0/20FN.Q1NrKwy.LFfcgIcmr8tEZ5trQeIYZAG', 'admin', 'active', '2026-03-05 16:49:31', NULL, NULL),
(7, 'asfa', 'asfa@gmail.com', '$2y$10$AmPznmtUzjTS17ALZdwX8.P3A9D0b..nw7eyuxdmmdG3QFGx/w6ua', 'doctor', 'active', '2026-03-07 21:52:16', NULL, NULL),
(12, 'raiba', 'raiba@gmail.com', '$2y$10$UMSCER0fxODeELYy9uzrKe6LeLqnzsUXGu6LBvEP/NQBi4So122jC', 'doctor', 'active', '2026-03-07 22:56:20', NULL, NULL),
(13, 'kiran', 'kiran@gmail.com', '$2y$10$pYd7bbSCfOyk2QG2wOtWtej4qAPBFA4.3JNxEHV7KTl9tCJBOBNaC', 'doctor', 'active', '2026-03-09 03:24:01', NULL, NULL),
(14, 'nimra', 'nimra@gmail.com', '$2y$10$4k.koNE2fOD88DEO2FBgp.bIndDkuedMELXX8XSu6tZbWyC03rxz.', 'doctor', 'active', '2026-03-09 03:26:21', NULL, NULL),
(15, 'sidra', 'sidra@gmail.com', '$2y$10$t80bVS9i.7pzgKCgoPovfOnx9Zt/TZ.K80MX7DcQjMtOMAvKNocNu', 'doctor', 'active', '2026-03-09 03:28:13', NULL, NULL),
(16, 'ahmed', 'ahmed@gmail.com', '$2y$10$/fC.IION/8dnO5Kow0Gx1egCZ3qnjMi3rqlNWOS9O0HfCWV9S0mgS', 'doctor', 'active', '2026-03-09 03:50:28', NULL, NULL),
(17, 'ahsan', 'ahsan@gmail.com', '$2y$10$0pVVZ8nHu8zQV8RSPF1wF./MfIUdeUWZKh4ciHh8ji1DklFzOjnpy', 'doctor', 'active', '2026-03-09 03:52:16', NULL, NULL),
(18, 'ayyan', 'ayyan@gmail.com', '$2y$10$1WNICTfGwxWpT.SOs.fGB.5I78YTEgwutOF3hYw6FTSNKsi0s27iC', 'doctor', 'active', '2026-03-09 03:53:46', NULL, NULL),
(19, 'fahad', 'fahad@gmail.com', '$2y$10$g9or7B59i2OMBP1UR.JSDOfGo9YpLnILj7X2oDffMbfyOPqa7b8b2', 'doctor', 'active', '2026-03-09 03:55:55', NULL, NULL),
(20, 'saad', 'saad@gmail.com', '$2y$10$3.X3IuQUCEATVrHlkMWYgOORPt5rlwOhMCJMML7mKxfqh.MYFptd.', 'doctor', 'active', '2026-03-09 03:57:20', NULL, NULL),
(21, 'affan', 'affan@gmail.com', '$2y$10$5Vt7kzuR.ZPco47XqxH2uu.P9nrZMjYBxFrBiJBuDFvyN2D8wimeW', 'patient', 'active', '2026-03-09 04:06:25', NULL, NULL),
(22, 'areeba', 'areeba@gmail.com', '$2y$10$l1x8iC.7rPecIc4lYUQ4rOx3/6adqmg69SPifpQ/ZDqOe4asCo76G', 'patient', 'active', '2026-03-09 04:17:37', NULL, NULL),
(23, 'sarim', 'sarim@gmail.com', '$2y$10$tnC/.Dn.HvBifI7HdchSiuNF6.AxPB1ExWddIhBEuAy3b0I4hXAze', 'patient', 'active', '2026-03-09 04:19:35', NULL, NULL),
(24, 'amna', 'amna@gmail.com', '$2y$10$I6Ha5/I.lzlryZYYmvQQ0OERR/WQBeYqWlxtoVQzpH1cvdEelKoj.', 'patient', 'active', '2026-03-09 04:21:36', NULL, NULL),
(25, 'afshan', 'afshan@gmail.com', '$2y$10$e43NbuIgNzGEaQZON7hsX.7SgY4wF8OMFlehHbrDMpYN1t/R7wV0m', 'patient', 'active', '2026-03-10 15:13:22', NULL, NULL),
(26, 'aiman', 'aiman@gmail.com', '$2y$10$/dRBiUx8QHmoQbGsvgGsxe7MUQLk3x39HQ6WCaNcmPnsudssNzB8.', 'patient', 'active', '2026-03-10 15:15:02', NULL, NULL),
(27, 'azlan', 'azlan@gmail.com', '$2y$10$XXCjfu9BEOW2rT4LvP9mPueiBWSbDGGrcYkOWPJJ/Lrwvbye4kAkK', 'patient', 'active', '2026-03-10 15:16:43', NULL, NULL),
(28, 'hamza', 'hamza@gmail.com', '$2y$10$JIgvSP9B6BvzzSy2wmCDbuWRtfafuaS/MwX1AYSFFGhcRkvtD584O', 'patient', 'active', '2026-03-10 15:18:28', NULL, NULL),
(29, 'kashaf', 'kashaf@gmail.com', '$2y$10$gAiu27AdfzkIVOo58WuKo./oyDfHdLxgysYQTdkLmxVSm9Lyl51wS', 'patient', 'active', '2026-03-10 15:19:48', NULL, NULL),
(30, 'umar', 'umar@gmail.com', '$2y$10$0toGiBVm4ypXXhE/arZ2ae8hF9lfjKeRW.KZ9gBrHxAFIbGWdo0Ka', 'patient', 'active', '2026-03-10 15:22:30', NULL, NULL),
(31, 'hamza00', 'hamzaa@gmail.com', '$2y$10$GzI43io2SxB9a6MzpMm91.AV29qRSEJ/hCu0omOfHaMhxlKZ10QVW', 'patient', 'active', '2026-03-11 09:11:45', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `city_name` (`city_name`);

--
-- Indexes for table `cures`
--
ALTER TABLE `cures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `disease_id` (`disease_id`);

--
-- Indexes for table `diseases`
--
ALTER TABLE `diseases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexes for table `medical_news`
--
ALTER TABLE `medical_news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexes for table `preventions`
--
ALTER TABLE `preventions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `disease_id` (`disease_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cures`
--
ALTER TABLE `cures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `diseases`
--
ALTER TABLE `diseases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `medical_news`
--
ALTER TABLE `medical_news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `preventions`
--
ALTER TABLE `preventions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `availability_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cures`
--
ALTER TABLE `cures`
  ADD CONSTRAINT `cures_ibfk_1` FOREIGN KEY (`disease_id`) REFERENCES `diseases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctors_ibfk_2` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `preventions`
--
ALTER TABLE `preventions`
  ADD CONSTRAINT `preventions_ibfk_1` FOREIGN KEY (`disease_id`) REFERENCES `diseases` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
