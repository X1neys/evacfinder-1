-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 06, 2026 at 07:28 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `evacfinder`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int NOT NULL,
  `announcement_id` varchar(20) NOT NULL,
  `ann_title` varchar(100) NOT NULL,
  `ann_type` varchar(50) NOT NULL,
  `ann_desc` varchar(255) NOT NULL,
  `encodedby` varchar(20) NOT NULL,
  `date_created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `announcement_id`, `ann_title`, `ann_type`, `ann_desc`, `encodedby`, `date_created`) VALUES
(1, 'ANN00001', '', 'General', 'testing', '1', '2026-05-25 01:41:50'),
(2, 'ANN00002', '', 'Advisory', 'next', '1', '2026-05-25 01:42:07'),
(3, 'ANN00003', '', 'General', 'asdsad', '1', '2026-05-26 00:37:53'),
(4, 'ANN00004', 'asdsa', 'General', 'asds', '1', '2026-05-26 00:41:11'),
(5, 'ANN00005', 'sfss', 'General', 'efs', '1', '2026-05-26 00:43:10'),
(6, 'ANN00006', 'TITLE', 'Advisory', 'blahbblahblahh', '1', '2026-05-26 12:34:36'),
(7, 'ANN00007', 'asd', 'Advisory', 'wasaf', '1', '2026-05-26 12:35:06'),
(8, 'ANN00008', 'asdw', 'Event', 'asd', '1', '2026-05-26 12:35:33'),
(9, 'ANN00009', '', '', '', '6', '2026-05-26 22:34:38'),
(10, 'ANN00010', '', '', '', '6', '2026-05-26 22:34:47'),
(11, 'ANN00011', '', '', '', '6', '2026-05-26 22:35:26'),
(12, 'ANN00012', '', '', '', '6', '2026-05-26 22:39:24'),
(13, 'ANN00013', 'Test ', 'General', 'Test annoucement', '00006', '2026-06-04 18:11:03');

-- --------------------------------------------------------

--
-- Table structure for table `centers`
--

CREATE TABLE `centers` (
  `id` int NOT NULL,
  `center_id` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `center_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `address` varchar(200) NOT NULL,
  `capacity` int NOT NULL,
  `max_persons` int NOT NULL,
  `current_occupants` int NOT NULL,
  `contact_number` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `contact_person` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `alternate_contact` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_established` date DEFAULT NULL,
  `facilities` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `hazard_type` varchar(300) NOT NULL,
  `remarks` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `encodedby` int NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `estimated_capacity` int DEFAULT NULL,
  `accessibility` varchar(300) DEFAULT NULL,
  `available_facilities` varchar(300) DEFAULT NULL,
  `assigned_lgu_user_id` varchar(10) DEFAULT NULL,
  `water_supply` varchar(50) DEFAULT NULL,
  `electricity` varchar(50) DEFAULT NULL,
  `num_rooms` int DEFAULT NULL,
  `has_wifi` tinyint(1) DEFAULT '0',
  `has_canteen` tinyint(1) DEFAULT '0',
  `has_medical` tinyint(1) DEFAULT '0',
  `restrooms_count` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `centers`
--

INSERT INTO `centers` (`id`, `center_id`, `center_name`, `category`, `status`, `barangay`, `city`, `province`, `address`, `capacity`, `max_persons`, `current_occupants`, `contact_number`, `contact_person`, `alternate_contact`, `date_established`, `facilities`, `hazard_type`, `remarks`, `encodedby`, `latitude`, `longitude`, `estimated_capacity`, `accessibility`, `available_facilities`, `assigned_lgu_user_id`, `water_supply`, `electricity`, `num_rooms`, `has_wifi`, `has_canteen`, `has_medical`, `restrooms_count`) VALUES
(1, 'EvacC00001', 'Banago Elementary School I', 'School', 'Inactive', '', '', 'Negros Occidental', '', 300, 0, 0, '', '', '', NULL, '', '', '', 1, NULL, NULL, 300, '', '', NULL, NULL, NULL, NULL, 0, 0, 0, NULL),
(2, 'EvacC00002', 'Alijis Hall', 'Community Center / Multipurpose Hall', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 500, 400, 1, '123', '', '', NULL, '', '', '\n[Activated on 2026-06-06 20:37:10]: \n[Activated on 2026-06-06 20:37:10]: \n[Activated on 2026-06-06 20:37:10]: \n[Activated on 2026-06-07 01:27:11] Capacity set to: 500 | Notes: \n[Activated on 2026-06-07 02:50:57] Capacity set to: 500 | Notes: ', 6, 10.65656800, 122.96001400, 400, '', '', '00006', 'Available', 'Available', 1, 1, 1, 1, 1);

--
-- Triggers `centers`
--
DELIMITER $$
CREATE TRIGGER `after_center_status_update` AFTER UPDATE ON `centers` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO `center_status_history` (`center_id`, `old_status`, `new_status`, `changed_by`, `changed_at`)
        VALUES (NEW.center_id, OLD.status, NEW.status, NULL, NOW());
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `center_history`
--

CREATE TABLE `center_history` (
  `history_id` int NOT NULL,
  `center_id` varchar(10) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text,
  `performed_by` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `center_history`
--

INSERT INTO `center_history` (`history_id`, `center_id`, `action_type`, `description`, `performed_by`, `created_at`) VALUES
(1, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee vxc xc status changed from Active to Departed', '00006', '2026-05-31 22:07:26'),
(3, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee vxc xc status changed from Departed to Active', '00006', '2026-05-31 22:09:21'),
(5, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Active to Departed', '00006', '2026-05-31 22:18:58'),
(6, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Active to Departed. Remarks: Depart\n', '00006', '2026-05-31 22:18:58'),
(7, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Departed to Active', '00006', '2026-05-31 22:19:23'),
(9, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee vxc xc status changed from Active to Active', '00006', '2026-05-31 22:27:32'),
(10, 'EvacC00002', 'EVACUEE_ADDED', 'Evacuee john Garcia was registered to the center', '00006', '2026-05-31 22:27:44'),
(11, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee john Garcia status changed from Active to Departed', '00006', '2026-05-31 22:28:13'),
(13, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee john Garcia status changed from Departed to Active', '00006', '2026-05-31 22:28:20'),
(17, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Active to Departed', '00006', '2026-06-04 17:28:39'),
(18, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee vxc xc status changed from Active to Departed', '00006', '2026-06-04 17:28:39'),
(19, 'EvacC00002', 'EVACUEE_DEPARTED', 'Evacuee Kit Garcia was automatically departed due to center deactivation', '00006', '2026-06-04 17:28:39'),
(20, 'EvacC00002', 'EVACUEE_DEPARTED', 'Evacuee vxc xc was automatically departed due to center deactivation', '00006', '2026-06-04 17:28:39'),
(21, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed to Inactive. 2 active evacuee(s) were automatically departed.', '00006', '2026-06-04 17:28:39'),
(22, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Active to Inactive. ', '00006', '2026-06-04 17:34:59'),
(23, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Inactive to Active. ', '00006', '2026-06-04 17:50:31'),
(24, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Active to Inactive. 3 evacuee(s) were cleared from this center.', '00006', '2026-06-04 17:59:48'),
(25, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Inactive to Active. Center reactivated for new operations.', '00006', '2026-06-04 18:00:03'),
(26, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Active to Inactive. No evacuees to clear.', '00006', '2026-06-04 18:00:12'),
(27, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Inactive to Active. Center reactivated for new operations.', '00006', '2026-06-04 18:01:39'),
(28, 'EvacC00002', 'EVACUEE_ADDED', 'Evacuee Kit Garcia was registered to the center', '00006', '2026-06-04 18:12:05'),
(29, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Active to Departed', '00006', '2026-06-04 20:24:49'),
(30, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Active to Departed', '00006', '2026-06-04 20:24:49'),
(31, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Departed to Active', '00006', '2026-06-04 20:24:58'),
(32, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Departed to Active', '00006', '2026-06-04 20:24:58'),
(33, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Active to Inactive. 1 evacuee(s) were cleared from this center.', '00006', '2026-06-06 19:33:59'),
(34, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Active to Inactive. No evacuees to clear.', '00006', '2026-06-06 21:41:21'),
(35, 'EvacC00002', 'EVACUEE_ADDED', 'Evacuee Kit Garcia was registered to the center', '00006', '2026-06-07 00:30:24'),
(36, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Active to Departed', '00006', '2026-06-07 01:04:40'),
(37, 'EvacC00002', 'CENTER_INACTIVATED', 'Center manually inactivated. 1 evacuee(s) were marked as departed. Active evacuees before inactivation: 1', '00006', '2026-06-07 01:04:40'),
(38, 'EvacC00002', 'CENTER_INACTIVATED', 'Center manually inactivated. 0 evacuee(s) were marked as departed. Active evacuees before inactivation: 0', '00006', '2026-06-07 01:48:13'),
(39, 'EvacC00002', 'CENTER_INACTIVATED', 'Center manually inactivated. 0 evacuee(s) were marked as departed. Active evacuees before inactivation: 0', '00006', '2026-06-07 02:03:31'),
(40, 'EvacC00002', 'CENTER_INACTIVATED', 'Center manually inactivated. 0 evacuee(s) were marked as departed. Active evacuees before inactivation: 0', '00006', '2026-06-07 02:05:13'),
(41, 'EvacC00002', 'CENTER_INACTIVATED', 'Center manually inactivated. 0 evacuee(s) were marked as departed. Active evacuees before inactivation: 0', '00006', '2026-06-07 02:07:23'),
(42, 'EvacC00002', 'CENTER_INACTIVATED', 'Center manually inactivated. No evacuees to clear. Active evacuees before inactivation: 0', '00006', '2026-06-07 02:51:07'),
(43, 'EvacC00002', 'CENTER_INACTIVATED', 'Center manually inactivated. No evacuees to clear. Active evacuees before inactivation: 0', '00006', '2026-06-07 03:04:17'),
(44, 'EvacC00002', 'EVACUEE_STATUS_CHANGE', 'Evacuee Kit Garcia status changed from Departed to Departed', '00006', '2026-06-07 03:07:50'),
(45, 'EvacC00002', 'CENTER_INACTIVATED', 'Center manually inactivated. No evacuees to clear. Active evacuees before inactivation: 0', '00006', '2026-06-07 03:10:14'),
(46, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Inactive to Active. Center reactivated for new operations.', '00006', '2026-06-07 03:16:35'),
(47, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Active to Active. ', '00006', '2026-06-07 03:16:36'),
(48, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Active to Active. ', '00006', '2026-06-07 03:16:36'),
(49, 'EvacC00002', 'CENTER_INACTIVATED', 'Center manually inactivated. No evacuees to clear. Active evacuees before inactivation: 0', '00006', '2026-06-07 03:16:41'),
(50, 'EvacC00002', 'CENTER_UPDATED', 'Center status changed from Inactive to Active. Center reactivated for new operations.', '00006', '2026-06-07 03:19:19'),
(51, 'EvacC00002', 'EVACUEE_ADDED', 'Evacuee 213 123 was registered to the center', '00006', '2026-06-07 03:19:34');

-- --------------------------------------------------------

--
-- Table structure for table `center_schedules`
--

CREATE TABLE `center_schedules` (
  `id` int NOT NULL,
  `schedule_id` varchar(20) NOT NULL,
  `center_id` varchar(10) NOT NULL,
  `scheduled_datetime` datetime NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `additional_info` text,
  `water_supply` varchar(50) DEFAULT NULL,
  `electricity` varchar(50) DEFAULT NULL,
  `num_rooms` int DEFAULT NULL,
  `has_wifi` tinyint(1) DEFAULT '0',
  `has_canteen` tinyint(1) DEFAULT '0',
  `has_medical` tinyint(1) DEFAULT '0',
  `restrooms_count` int DEFAULT NULL,
  `notes` text,
  `created_by` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `executed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `center_schedules`
--

INSERT INTO `center_schedules` (`id`, `schedule_id`, `center_id`, `scheduled_datetime`, `status`, `additional_info`, `water_supply`, `electricity`, `num_rooms`, `has_wifi`, `has_canteen`, `has_medical`, `restrooms_count`, `notes`, `created_by`, `created_at`, `executed_at`) VALUES
(1, 'SCH00001', 'EvacC00002', '2026-06-06 20:21:00', 'Executed', '{\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"20\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"10\",\"notes\":\"\"}', 'Available', 'Available', 20, 1, 1, 1, 10, '', '00006', '2026-06-06 20:20:17', '2026-06-06 20:37:10'),
(2, 'SCH00002', 'EvacC00002', '2026-06-06 20:25:00', 'Executed', '{\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"21\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"12\",\"notes\":\"\"}', 'Available', 'Available', 21, 1, 1, 1, 12, '', '00006', '2026-06-06 20:24:08', '2026-06-06 20:37:10'),
(3, 'SCH00003', 'EvacC00002', '2026-06-06 20:36:00', 'Executed', '{\"water_supply\":\"Available\",\"electricity\":\"\",\"num_rooms\":\"20\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"10\",\"notes\":\"\"}', 'Available', '', 20, 1, 1, 1, 10, '', '00006', '2026-06-06 20:35:37', '2026-06-06 20:37:10'),
(4, 'SCH00004', 'EvacC00002', '2026-06-06 20:48:00', 'Executed', '{\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"10\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"10\",\"notes\":\"\"}', 'Available', 'Available', 10, 1, 1, 1, 10, '', '00006', '2026-06-06 20:47:21', '2026-06-06 21:40:56'),
(5, 'SCH00005', 'EvacC00002', '2026-06-06 20:52:00', 'Executed', '{\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"19\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"13\",\"notes\":\"\"}', 'Available', 'Available', 19, 1, 1, 1, 13, '', '00006', '2026-06-06 20:51:32', '2026-06-06 21:40:56'),
(6, 'SCH00006', 'EvacC00002', '2026-06-06 21:02:00', 'Executed', '{\"water_supply\":\"Available\",\"electricity\":\"Generator\",\"num_rooms\":\"0\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"0\",\"notes\":\"\"}', 'Available', 'Generator', 0, 1, 1, 1, 0, '', '00006', '2026-06-06 21:01:10', '2026-06-06 21:40:56'),
(7, 'SCH00007', 'EvacC00002', '2026-06-06 21:16:00', 'Executed', '{\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-06 21:15:31', '2026-06-06 21:40:56'),
(8, 'SCH00008', 'EvacC00002', '2026-06-06 21:18:00', 'Executed', '{\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"0\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"0\",\"notes\":\"\"}', 'Available', 'Available', 0, 1, 1, 1, 0, '', '00006', '2026-06-06 21:17:06', '2026-06-06 21:40:56'),
(9, 'SCH00009', 'EvacC00002', '2026-06-06 21:46:00', 'Executed', NULL, 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-06 21:45:28', '2026-06-06 21:46:03'),
(10, 'SCH00010', 'EvacC00002', '2026-06-07 23:50:00', 'Cancelled', '{\"capacity\":\"400\",\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"4\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"4\",\"notes\":\"\"}', 'Available', 'Available', 4, 1, 1, 1, 4, '', '00006', '2026-06-06 23:48:34', NULL),
(11, 'SCH00011', 'EvacC00002', '2026-06-06 23:55:00', 'Executed', '{\"capacity\":\"400\",\"water_supply\":\"Available\",\"electricity\":\"\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', '', 1, 1, 1, 1, 1, '', '00006', '2026-06-06 23:52:44', '2026-06-06 23:59:57'),
(12, 'SCH00012', 'EvacC00002', '2026-06-07 00:30:00', 'Executed', '{\"capacity\":\"500\",\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-07 00:29:03', '2026-06-07 00:30:10'),
(13, 'SCH00013', 'EvacC00002', '2026-06-07 01:27:00', 'Executed', '{\"capacity\":\"500\",\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-07 01:25:24', '2026-06-07 01:27:11'),
(14, 'SCH00014', 'EvacC00002', '2026-06-07 03:01:00', 'Cancelled', '{\"capacity\":\"500\",\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-07 02:01:51', NULL),
(15, 'SCH00015', 'EvacC00002', '2026-06-07 02:03:00', 'Executed', '{\"capacity\":\"500\",\"water_supply\":\"Limited\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Limited', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-07 02:02:13', '2026-06-07 02:03:25'),
(16, 'SCH00016', 'EvacC00002', '2026-06-07 02:05:00', 'Executed', '{\"capacity\":\"500\",\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-07 02:04:09', '2026-06-07 02:05:04'),
(17, 'SCH00017', 'EvacC00002', '2026-06-07 02:07:00', 'Executed', '{\"capacity\":\"500\",\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-07 02:06:19', '2026-06-07 02:07:19'),
(18, 'SCH00018', 'EvacC00002', '2026-06-07 02:50:00', 'Executed', '{\"capacity\":\"500\",\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-07 02:48:55', '2026-06-07 02:50:57'),
(19, 'SCH00019', 'EvacC00002', '2026-06-07 03:04:00', 'Executed', '{\"capacity\":\"500\",\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-07 03:02:05', '2026-06-07 03:04:03'),
(20, 'SCH00020', 'EvacC00002', '2026-06-07 03:10:00', 'Executed', '{\"capacity\":\"500\",\"water_supply\":\"Available\",\"electricity\":\"Available\",\"num_rooms\":\"1\",\"has_wifi\":1,\"has_canteen\":1,\"has_medical\":1,\"restrooms_count\":\"1\",\"notes\":\"\"}', 'Available', 'Available', 1, 1, 1, 1, 1, '', '00006', '2026-06-07 03:08:04', '2026-06-07 03:10:09');

-- --------------------------------------------------------

--
-- Table structure for table `center_status_history`
--

CREATE TABLE `center_status_history` (
  `history_id` int NOT NULL,
  `center_id` varchar(10) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` varchar(10) DEFAULT NULL,
  `changed_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `center_status_history`
--

INSERT INTO `center_status_history` (`history_id`, `center_id`, `old_status`, `new_status`, `changed_by`, `changed_at`) VALUES
(1, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-05-31 22:25:15'),
(2, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-05-31 22:27:17'),
(3, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-04 17:28:39'),
(4, 'EvacC00002', 'Inactive', 'Inactive', '00006', '2026-06-04 17:28:39'),
(5, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-04 17:28:56'),
(6, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-04 17:34:59'),
(7, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-04 17:34:59'),
(8, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-04 17:50:31'),
(9, 'EvacC00002', 'Inactive', 'Active', '00006', '2026-06-04 17:50:31'),
(10, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-04 17:59:48'),
(11, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-04 17:59:48'),
(12, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-04 18:00:03'),
(13, 'EvacC00002', 'Inactive', 'Active', '00006', '2026-06-04 18:00:03'),
(14, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-04 18:00:12'),
(15, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-04 18:00:12'),
(16, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-04 18:01:39'),
(17, 'EvacC00002', 'Inactive', 'Active', '00006', '2026-06-04 18:01:39'),
(18, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-06 19:33:59'),
(19, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-06 19:33:59'),
(20, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-06 20:37:10'),
(21, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-06 20:46:51'),
(22, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-06 21:40:56'),
(23, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-06 21:41:21'),
(24, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-06 21:41:21'),
(25, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-06 21:46:03'),
(26, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-06 22:07:09'),
(27, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-06 23:59:57'),
(28, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 00:28:35'),
(29, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 00:30:10'),
(30, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 01:04:40'),
(31, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-07 01:04:40'),
(32, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 01:27:11'),
(33, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 01:48:13'),
(34, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-07 01:48:13'),
(35, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 02:03:25'),
(36, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 02:03:31'),
(37, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-07 02:03:31'),
(38, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 02:05:04'),
(39, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 02:05:13'),
(40, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-07 02:05:13'),
(41, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 02:07:19'),
(42, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 02:07:23'),
(43, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-07 02:07:23'),
(44, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 02:50:57'),
(45, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 02:51:07'),
(46, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-07 02:51:07'),
(47, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 03:04:03'),
(48, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 03:04:17'),
(49, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-07 03:04:17'),
(50, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 03:10:09'),
(51, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 03:10:14'),
(52, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-07 03:10:14'),
(53, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 03:16:35'),
(54, 'EvacC00002', 'Inactive', 'Active', '00006', '2026-06-07 03:16:35'),
(55, 'EvacC00002', 'Active', 'Active', '00006', '2026-06-07 03:16:36'),
(56, 'EvacC00002', 'Active', 'Active', '00006', '2026-06-07 03:16:36'),
(57, 'EvacC00002', 'Active', 'Inactive', NULL, '2026-06-07 03:16:40'),
(58, 'EvacC00002', 'Active', 'Inactive', '00006', '2026-06-07 03:16:41'),
(59, 'EvacC00002', 'Inactive', 'Active', NULL, '2026-06-07 03:19:19'),
(60, 'EvacC00002', 'Inactive', 'Active', '00006', '2026-06-07 03:19:19');

-- --------------------------------------------------------

--
-- Table structure for table `daily_center_reports`
--

CREATE TABLE `daily_center_reports` (
  `id` int NOT NULL,
  `center_id` varchar(10) NOT NULL,
  `center_name` varchar(100) NOT NULL,
  `report_date` date NOT NULL,
  `active_evacuees` int DEFAULT '0',
  `arrivals_today` int DEFAULT '0',
  `departures_today` int DEFAULT '0',
  `center_status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `daily_center_reports`
--

INSERT INTO `daily_center_reports` (`id`, `center_id`, `center_name`, `report_date`, `active_evacuees`, `arrivals_today`, `departures_today`, `center_status`, `created_at`) VALUES
(1, 'EvacC00001', 'Banago Elementary School I', '2026-06-04', 0, 0, 0, 'Inactive', '2026-06-04 09:50:57'),
(2, 'EvacC00002', 'Alijis Hall', '2026-06-04', 0, 0, 2, 'Active', '2026-06-04 09:50:57');

-- --------------------------------------------------------

--
-- Table structure for table `evacuees`
--

CREATE TABLE `evacuees` (
  `id` int NOT NULL,
  `evacuee_id` varchar(20) NOT NULL,
  `registration_date` date NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `extension_name` varchar(20) DEFAULT NULL,
  `relation_to_head` varchar(50) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `age` int DEFAULT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `complete_address` varchar(200) DEFAULT NULL,
  `emergency_contact_person` varchar(100) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `condition_pregnant` tinyint(1) DEFAULT '0',
  `condition_lactating` tinyint(1) DEFAULT '0',
  `condition_elderly` tinyint(1) DEFAULT '0',
  `condition_pwd` tinyint(1) DEFAULT '0',
  `condition_4ps` tinyint(1) DEFAULT '0',
  `pwd_type` varchar(50) DEFAULT NULL,
  `health_status` varchar(50) DEFAULT NULL,
  `emergency_medical_condition` varchar(100) DEFAULT NULL,
  `medications_taken` text,
  `known_allergies` text,
  `evacuation_center_id` varchar(10) DEFAULT NULL,
  `arrival_date` date DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `evacuee_status` varchar(20) DEFAULT 'Active',
  `encodedby` varchar(5) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `registered_by_lgu_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `evacuees`
--

INSERT INTO `evacuees` (`id`, `evacuee_id`, `registration_date`, `last_name`, `first_name`, `middle_name`, `extension_name`, `relation_to_head`, `sex`, `birth_date`, `age`, `civil_status`, `occupation`, `contact_number`, `complete_address`, `emergency_contact_person`, `emergency_contact_number`, `condition_pregnant`, `condition_lactating`, `condition_elderly`, `condition_pwd`, `condition_4ps`, `pwd_type`, `health_status`, `emergency_medical_condition`, `medications_taken`, `known_allergies`, `evacuation_center_id`, `arrival_date`, `departure_date`, `evacuee_status`, `encodedby`, `created_at`, `registered_by_lgu_id`) VALUES
(1, 'Evac00001', '2026-05-26', 'Garcia', 'Kit', '', '', '', 'Male', NULL, NULL, '', '', '', '', '', '', 0, 0, 0, 0, 0, '', '', '', '', '', NULL, '2026-05-26', '2026-06-04', 'Departed', '00006', '2026-05-26 14:52:14', NULL),
(2, 'Evac00002', '2026-05-30', 'xc', 'vxc', '', '', '', 'Other', NULL, NULL, '', '', '', '', '', '', 0, 0, 0, 0, 0, '', '', '', '', '', NULL, '2026-05-30', '2026-06-04', 'Departed', '00006', '2026-05-30 14:10:44', '00006'),
(3, 'Evac00003', '2026-05-31', 'Garcia', 'john', '', '', '', 'Male', NULL, NULL, '', '', '', '', '', '', 0, 0, 0, 0, 0, '', '', '', '', '', NULL, '2026-05-31', '2026-05-31', 'Departed', '00006', '2026-05-31 14:27:44', '00006'),
(4, 'Evac00004', '2026-06-04', 'Garcia', 'Kit', '', '', '', 'Male', NULL, NULL, '', '', '', '', '', '', 0, 0, 1, 0, 0, 'Other', 'Good', 'None', '', '', NULL, '2026-06-04', '2026-06-04', 'Active', '00006', '2026-06-04 10:12:05', '00006'),
(5, 'Evac00005', '2026-06-06', 'Garcia', 'Kit', '', '', '', 'Female', NULL, NULL, '', '', '', '', '', '', 1, 1, 1, 1, 1, '', '', '', '', '', 'EvacC00002', '2026-06-06', '2026-06-07', 'Departed', '00006', '2026-06-06 16:30:24', '00006'),
(6, 'Evac00006', '2026-06-06', '123', '213', '', '', '', 'Male', NULL, NULL, '', '', '', '', '', '', 1, 1, 1, 1, 1, '', '', '', '', '', 'EvacC00002', '2026-06-06', NULL, 'Active', '00006', '2026-06-06 19:19:34', '00006');

--
-- Triggers `evacuees`
--
DELIMITER $$
CREATE TRIGGER `after_evacuee_insert` AFTER INSERT ON `evacuees` FOR EACH ROW BEGIN
    INSERT INTO `center_history` (`center_id`, `action_type`, `description`, `performed_by`, `created_at`)
    VALUES (
        NEW.evacuation_center_id, 
        'EVACUEE_ADDED', 
        CONCAT('Evacuee ', NEW.first_name, ' ', NEW.last_name, ' was registered to the center'), 
        NEW.encodedby, 
        NOW()
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_evacuee_status_update` AFTER UPDATE ON `evacuees` FOR EACH ROW BEGIN
    IF OLD.evacuee_status != NEW.evacuee_status THEN
        INSERT INTO `center_history` (`center_id`, `action_type`, `description`, `performed_by`, `created_at`)
        VALUES (
            NEW.evacuation_center_id, 
            'EVACUEE_STATUS_CHANGE', 
            CONCAT('Evacuee ', NEW.first_name, ' ', NEW.last_name, ' status changed from ', OLD.evacuee_status, ' to ', NEW.evacuee_status), 
            NEW.encodedby, 
            NOW()
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int NOT NULL,
  `center_id` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `center_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `address` varchar(200) NOT NULL,
  `capacity` int NOT NULL,
  `max_persons` int NOT NULL,
  `current_occupants` int NOT NULL,
  `contact_number` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `contact_person` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_established` date DEFAULT NULL,
  `facilities` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `remarks` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `encodedby` int NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `estimated_capacity` int DEFAULT NULL,
  `accessibility` varchar(300) DEFAULT NULL,
  `available_facilities` varchar(300) DEFAULT NULL,
  `history_date` datetime DEFAULT NULL,
  `action_made` varchar(100) NOT NULL,
  `assigned_lgu_user_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id`, `center_id`, `center_name`, `category`, `status`, `barangay`, `city`, `province`, `address`, `capacity`, `max_persons`, `current_occupants`, `contact_number`, `contact_person`, `date_established`, `facilities`, `remarks`, `encodedby`, `latitude`, `longitude`, `estimated_capacity`, `accessibility`, `available_facilities`, `history_date`, `action_made`, `assigned_lgu_user_id`) VALUES
(1, 'EvacC00001', 'Banago Elem', 'Secondary', 'Inactive', '', '', 'Negros Occidental', '', 300, 0, 0, '', '', NULL, '', '', 1, NULL, NULL, 300, '', '', NULL, '0', NULL),
(2, 'EvacC00002', 'Alijis', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis', 400, 400, 1, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', NULL, '0', '00006'),
(3, 'EvacC00002', 'Alijis', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', '', 400, 400, 1, '', '', NULL, '', 'hmm', 6, NULL, NULL, 400, '', '', '2026-05-31 11:31:00', 'Updated', '00006'),
(4, 'EvacC00002', 'Alijis Hall weh??', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-02 22:14:13', 'Updated', '00006'),
(5, 'EvacC00002', 'Alijis Hall wah', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-02 22:14:45', 'Updated', '00006'),
(6, 'EvacC00002', 'Alijis Hall sure na?', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-02 22:22:03', 'Updated', '00006'),
(7, 'EvacC00002', 'Alijis Hall isa pa', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-02 22:39:27', 'Updated', '00006'),
(8, 'EvacC00002', 'Alijis Hall sige daw bi?', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-02 23:03:07', 'Updated', '00006'),
(9, 'EvacC00002', 'Alijis Hall change bi', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-02 23:04:02', 'Updated', '00006'),
(10, 'EvacC00002', 'Alijis Hall sige', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 3, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 13:13:52', 'Updated', '00006'),
(11, 'EvacC00002', 'Alijis Hall try', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 3, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:03:01', 'Updated', '00006'),
(12, 'EvacC00002', 'Alijis Hall try bi', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 3, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:03:51', 'Updated', '00006'),
(13, 'EvacC00002', 'Alijis Hall liwat na naman?', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 3, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:08:39', 'Updated', '00006'),
(14, 'EvacC00002', 'Alijis Hall allen', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 3, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:14:34', 'Updated', '00006'),
(15, 'EvacC00002', 'Alijis Hall patya nlng ko bi?', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 3, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:39:58', 'Updated', '00006'),
(16, 'EvacC00002', 'Alijis Hall talaka', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 3, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:41:31', 'Updated', '00006'),
(17, 'EvacC00002', 'wow', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:42:24', 'Updated', '00006'),
(18, 'EvacC00002', 'Alijis Hall ', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:43:04', 'Updated', '00006'),
(19, 'EvacC00002', 'wow', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:43:23', 'Updated', '00006'),
(20, 'EvacC00001', 'edit', 'Secondary', 'Inactive', '', '', 'Negros Occidental', '', 300, 0, 0, '', '', NULL, '', '', 6, NULL, NULL, 300, '', '', '2026-06-03 14:43:35', 'Updated', NULL),
(21, 'EvacC00002', 'talaga?', 'Primary', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-03 14:44:07', 'Updated', '00006'),
(22, 'EvacC00002', 'Alijis Hall', 'School', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 2, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-04 17:27:45', 'Updated', '00006'),
(23, 'EvacC00001', 'Banago Elementary School I', 'School', 'Inactive', '', '', 'Negros Occidental', '', 300, 0, 0, '', '', NULL, '', '', 6, NULL, NULL, 300, '', '', '2026-06-04 17:28:15', 'Updated', NULL),
(24, 'EvacC00002', 'Alijis Hall', 'School', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 0, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-04 17:28:56', 'Updated', '00006'),
(25, 'EvacC00002', 'Alijis Hall', 'Community Center / Multipurpose Hall', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 1, '', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-04 20:13:04', 'Updated', '00006'),
(26, 'EvacC00002', 'Alijis Hall', 'Community Center / Multipurpose Hall', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 400, 400, 1, '123', '', NULL, '', '', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-04 20:25:14', 'Updated', '00006'),
(27, 'EvacC00002', 'Alijis Hall', 'Community Center / Multipurpose Hall', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 0, 400, 1, '123', '', NULL, '', '\n[Activated on 2026-06-06 20:37:10]:  - Auto-activated via schedule: ', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-06 20:37:10', 'Auto-Activated', '00006'),
(28, 'EvacC00002', 'Alijis Hall', 'Community Center / Multipurpose Hall', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 0, 400, 1, '123', '', NULL, '', '\n[Activated on 2026-06-06 20:37:10]: \n[Activated on 2026-06-06 20:37:10]:  - Auto-activated via schedule: ', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-06 20:37:10', 'Auto-Activated', '00006'),
(29, 'EvacC00002', 'Alijis Hall', 'Community Center / Multipurpose Hall', 'Active', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 0, 400, 1, '123', '', NULL, '', '\n[Activated on 2026-06-06 20:37:10]: \n[Activated on 2026-06-06 20:37:10]: \n[Activated on 2026-06-06 20:37:10]:  - Auto-activated via schedule: ', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-06 20:37:10', 'Auto-Activated', '00006'),
(30, 'EvacC00002', 'Alijis Hall', 'Community Center / Multipurpose Hall', 'Inactive', 'Alijis', 'Bacolod', 'Negros Occidental', 'Alijis bcd', 0, 400, 0, '123', '', NULL, '', '\n[Activated on 2026-06-06 20:37:10]: \n[Activated on 2026-06-06 20:37:10]: \n[Activated on 2026-06-06 20:37:10]: ', 6, 10.65656800, 122.96001400, 400, '', '', '2026-06-06 20:46:51', 'Updated', '00006');

-- --------------------------------------------------------

--
-- Table structure for table `lgu_center_assignments`
--

CREATE TABLE `lgu_center_assignments` (
  `id` int NOT NULL,
  `lgu_user_id` varchar(10) NOT NULL,
  `center_id` varchar(10) NOT NULL,
  `assigned_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` varchar(10) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lgu_center_assignments`
--

INSERT INTO `lgu_center_assignments` (`id`, `lgu_user_id`, `center_id`, `assigned_date`, `assigned_by`, `status`) VALUES
(1, '00006', 'EvacC00002', '2026-05-28 02:33:54', '00006', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `lgu_users`
--

CREATE TABLE `lgu_users` (
  `id` int NOT NULL,
  `lgu_id` varchar(10) NOT NULL,
  `lgu_office_name` varchar(100) NOT NULL,
  `office_email_address` varchar(100) NOT NULL,
  `office_type` varchar(50) NOT NULL,
  `province` varchar(100) DEFAULT NULL,
  `region` varchar(100) NOT NULL,
  `position_role` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `office_number` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lgu_users`
--

INSERT INTO `lgu_users` (`id`, `lgu_id`, `lgu_office_name`, `office_email_address`, `office_type`, `province`, `region`, `position_role`, `first_name`, `last_name`, `office_number`, `contact_number`, `password`, `registration_date`, `status`) VALUES
(2, 'LGU00001', 'based', 'allen@gmail.com', 'municipal', 'negros-occidental', 'region-vi', 'pretty', 'allen', 'sarmiento', '09491744739', NULL, 'me', '2026-05-26 04:54:08', 'Pending'),
(3, 'LGU00003', 'Bacolod', '123@gmail.com', 'city', 'negros-occidental', 'region-vi', 'Okay', 'john', 'Garcia', '12312321', NULL, '$2y$10$cquRr.F/ktHdBlYnw2vkA.0oCDbkkYzzpKm2tK/CgJabJwaQ4N5ue', '2026-05-26 08:42:47', 'Pending'),
(4, '00007', 'asd', 'asdfgh@gmail.com', 'municipal', 'negros-occidental', 'region-vi', 'Center Manager', 'QWER', 'ASDF', '12', '12', '$2y$10$jpBgeZuW7e/qb0lbEyQZUu1fbRljsOAPSbiM/nJWPVwh69NMhLKny', '2026-06-04 16:46:18', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `personal_users`
--

CREATE TABLE `personal_users` (
  `id` int NOT NULL,
  `user_id` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_initial` varchar(2) DEFAULT NULL,
  `extension` varchar(10) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `sex` varchar(10) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `region` varchar(100) NOT NULL,
  `account_type` varchar(50) DEFAULT 'User',
  `password` varchar(255) NOT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personal_users`
--

INSERT INTO `personal_users` (`id`, `user_id`, `first_name`, `last_name`, `middle_initial`, `extension`, `date_of_birth`, `sex`, `email_address`, `phone_number`, `region`, `account_type`, `password`, `registration_date`, `status`) VALUES
(9, '00004', 'lance', 'sarmiento', 'G', 'hey', '2005-12-12', 'Male', 'lancesarmiento40@gmail.com', '09491744739', 'negros-occidental-region6', 'Public User', '123', '2026-05-26 07:39:32', 'Active'),
(10, '00005', 'john', 'Garcia', 'P', '', '2026-04-30', 'Male', 'capkeith43@gmail.com', '09494949494', 'negros-occidental-region6', 'Public User', '123', '2026-05-26 08:41:52', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `saved_reports`
--

CREATE TABLE `saved_reports` (
  `id` int NOT NULL,
  `report_id` varchar(50) NOT NULL,
  `center_id` varchar(10) NOT NULL,
  `center_name` varchar(100) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `generated_by` varchar(10) NOT NULL,
  `generated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `saved_reports`
--

INSERT INTO `saved_reports` (`id`, `report_id`, `center_id`, `center_name`, `report_type`, `file_path`, `generated_by`, `generated_at`) VALUES
(5, 'RPT-00001', 'EvacC00002', 'Alijis Hall', 'Inactivation', 'reports/inactivation_reports/RPT-00001.html', '00006', '2026-06-07 03:16:43');

-- --------------------------------------------------------

--
-- Table structure for table `userrights`
--

CREATE TABLE `userrights` (
  `id` int NOT NULL,
  `userid` varchar(5) NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Type` varchar(10) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `userrights`
--

INSERT INTO `userrights` (`id`, `userid`, `email`, `password`, `Type`, `last_login`, `status`) VALUES
(1, '00001', 'sample@gmail.com', 'user', '', '2026-05-26 08:41:12', 'Active'),
(7, '00003', 'allen@gmail.com', 'me', 'lgu', '2026-06-04 11:29:59', 'Active'),
(8, '00004', 'lancesarmiento40@gmail.com', '123', 'public', '2026-05-26 07:39:47', 'Active'),
(9, '00005', 'capkeith43@gmail.com', '123', 'public', '2026-06-04 11:29:35', 'Active'),
(10, '00006', '123@gmail.com', '$2y$10$cquRr.F/ktHdBlYnw2vkA.0oCDbkkYzzpKm2tK/CgJabJwaQ4N5ue', 'lgu', '2026-06-06 19:07:38', 'Active'),
(11, '00007', '1arveehayahay25@gmail.com', '$2y$10$jpBgeZuW7e/qb0lbEyQZUu1fbRljsOAPSbiM/nJWPVwh69NMhLKny', 'lgu', NULL, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `userid` varchar(20) NOT NULL,
  `permissions` text,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `centers`
--
ALTER TABLE `centers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `center_id` (`center_id`),
  ADD KEY `fk_centers_assigned_lgu` (`assigned_lgu_user_id`);

--
-- Indexes for table `center_history`
--
ALTER TABLE `center_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_center_history_center` (`center_id`),
  ADD KEY `idx_center_history_created` (`created_at`);

--
-- Indexes for table `center_schedules`
--
ALTER TABLE `center_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `schedule_id` (`schedule_id`),
  ADD KEY `fk_schedule_center` (`center_id`);

--
-- Indexes for table `center_status_history`
--
ALTER TABLE `center_status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_status_history_center` (`center_id`),
  ADD KEY `idx_status_history_changed` (`changed_at`);

--
-- Indexes for table `daily_center_reports`
--
ALTER TABLE `daily_center_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_daily_report` (`center_id`,`report_date`);

--
-- Indexes for table `evacuees`
--
ALTER TABLE `evacuees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `evacuee_id` (`evacuee_id`),
  ADD KEY `evacuation_center_id` (`evacuation_center_id`),
  ADD KEY `fk_evacuees_registered_by` (`registered_by_lgu_id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_centers_assigned_lgu` (`assigned_lgu_user_id`);

--
-- Indexes for table `lgu_center_assignments`
--
ALTER TABLE `lgu_center_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`lgu_user_id`,`center_id`),
  ADD KEY `fk_lgu_assignments_center` (`center_id`);

--
-- Indexes for table `lgu_users`
--
ALTER TABLE `lgu_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lgu_id` (`lgu_id`),
  ADD UNIQUE KEY `office_email_address` (`office_email_address`);

--
-- Indexes for table `personal_users`
--
ALTER TABLE `personal_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email_address` (`email_address`);

--
-- Indexes for table `saved_reports`
--
ALTER TABLE `saved_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_id` (`report_id`),
  ADD KEY `idx_center_id` (`center_id`);

--
-- Indexes for table `userrights`
--
ALTER TABLE `userrights`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_userid` (`userid`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`userid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `centers`
--
ALTER TABLE `centers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `center_history`
--
ALTER TABLE `center_history`
  MODIFY `history_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `center_schedules`
--
ALTER TABLE `center_schedules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `center_status_history`
--
ALTER TABLE `center_status_history`
  MODIFY `history_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `daily_center_reports`
--
ALTER TABLE `daily_center_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `evacuees`
--
ALTER TABLE `evacuees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `lgu_center_assignments`
--
ALTER TABLE `lgu_center_assignments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lgu_users`
--
ALTER TABLE `lgu_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `personal_users`
--
ALTER TABLE `personal_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `saved_reports`
--
ALTER TABLE `saved_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `userrights`
--
ALTER TABLE `userrights`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `centers`
--
ALTER TABLE `centers`
  ADD CONSTRAINT `fk_centers_assigned_lgu` FOREIGN KEY (`assigned_lgu_user_id`) REFERENCES `userrights` (`userid`) ON DELETE SET NULL;

--
-- Constraints for table `center_history`
--
ALTER TABLE `center_history`
  ADD CONSTRAINT `fk_center_history_center` FOREIGN KEY (`center_id`) REFERENCES `centers` (`center_id`) ON DELETE CASCADE;

--
-- Constraints for table `center_schedules`
--
ALTER TABLE `center_schedules`
  ADD CONSTRAINT `fk_schedule_center` FOREIGN KEY (`center_id`) REFERENCES `centers` (`center_id`) ON DELETE CASCADE;

--
-- Constraints for table `center_status_history`
--
ALTER TABLE `center_status_history`
  ADD CONSTRAINT `fk_status_history_center` FOREIGN KEY (`center_id`) REFERENCES `centers` (`center_id`) ON DELETE CASCADE;

--
-- Constraints for table `evacuees`
--
ALTER TABLE `evacuees`
  ADD CONSTRAINT `fk_evacuees_center` FOREIGN KEY (`evacuation_center_id`) REFERENCES `centers` (`center_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_evacuees_registered_by` FOREIGN KEY (`registered_by_lgu_id`) REFERENCES `userrights` (`userid`) ON DELETE SET NULL;

--
-- Constraints for table `lgu_center_assignments`
--
ALTER TABLE `lgu_center_assignments`
  ADD CONSTRAINT `fk_lgu_assignments_center` FOREIGN KEY (`center_id`) REFERENCES `centers` (`center_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lgu_assignments_user` FOREIGN KEY (`lgu_user_id`) REFERENCES `userrights` (`userid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
