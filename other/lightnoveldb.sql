-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 17, 2025 at 12:22 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lightnoveldb`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(3, 'Action'),
(4, 'Adventure'),
(5, 'Comedy'),
(1, 'Fantasy'),
(2, 'Romance');

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `chapter_id` int NOT NULL,
  `novel_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `content` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `chapters`
--

INSERT INTO `chapters` (`chapter_id`, `novel_id`, `title`, `content`, `created_at`) VALUES
(12, 8, 'Chương 1: Thế giới của Yggdrasil', 'Momonga nhận ra mình bị mắc kẹt trong thế giới game...', '2025-03-17 00:42:31'),
(13, 8, 'Chương 2: Bắt đầu cuộc chinh phục', 'Albedo và các thuộc hạ trung thành lên kế hoạch kiểm soát thế giới mới.', '2025-03-17 00:42:31'),
(14, 8, 'Chương 3: Đối đầu với kẻ thù đầu tiên', 'Momonga thể hiện sức mạnh áp đảo của mình trước một vương quốc nhỏ.', '2025-03-17 00:42:31'),
(15, 8, 'Chương 4: Bí mật của Nazarick', 'Những bí mật của Great Tomb of Nazarick dần được hé lộ.', '2025-03-17 00:42:31'),
(16, 8, 'Chương 5: Lời tuyên bố của Overlord', 'Momonga chính thức tuyên bố chinh phục thế giới.', '2025-03-17 00:42:31'),
(17, 9, 'Chương 1: Đăng nhập vào SAO', 'Kirito bước vào thế giới Aincrad đầy thử thách.', '2025-03-17 00:42:31'),
(18, 9, 'Chương 2: Trận chiến đầu tiên', 'Kirito gặp Asuna và chiến đấu với quái vật đầu tiên.', '2025-03-17 00:42:31'),
(19, 9, 'Chương 3: Tầng đầu tiên của Aincrad', 'Nhóm chiến binh quyết định chiến đấu với boss tầng 1.', '2025-03-17 00:42:31'),
(20, 9, 'Chương 4: Hội Moonlit Black Cats', 'Kirito gia nhập một guild và gặp bi kịch lớn.', '2025-03-17 00:42:31'),
(21, 9, 'Chương 5: Trận chiến với Boss tầng 10', 'Một trận chiến cam go để vượt qua tầng 10.', '2025-03-17 00:42:31'),
(22, 10, 'Chương 1: Subaru bị triệu hồi', 'Subaru xuất hiện tại một thế giới kỳ lạ...', '2025-03-17 00:42:31'),
(23, 10, 'Chương 2: Cái chết đầu tiên', 'Subaru phát hiện ra khả năng quay lại từ cái chết.', '2025-03-17 00:42:31'),
(24, 10, 'Chương 3: Gặp gỡ Emilia', 'Subaru gặp cô gái bí ẩn mang tên Emilia.', '2025-03-17 00:42:31'),
(25, 10, 'Chương 4: Bí ẩn của nhà Roswaal', 'Subaru bị cuốn vào các âm mưu trong dinh thự Roswaal.', '2025-03-17 00:42:31'),
(26, 10, 'Chương 5: Đối mặt với quái thú', 'Subaru và nhóm bạn phải chiến đấu với một con quái vật khổng lồ.', '2025-03-17 00:42:31'),
(27, 11, 'Chương 1: Thế giới không có chiến tranh', 'Sora và Shiro bị triệu hồi đến thế giới nơi tất cả đều được quyết định bằng trò chơi.', '2025-03-17 00:42:31'),
(28, 11, 'Chương 2: Trận đấu cờ sinh tử', 'Sora và Shiro đối đầu với một cao thủ cờ vua.', '2025-03-17 00:42:31'),
(29, 11, 'Chương 3: Đánh bại công chúa', 'Sora đánh bại công chúa của vương quốc Imanity.', '2025-03-17 00:42:31'),
(30, 11, 'Chương 4: Cuộc chiến trí tuệ', 'Một trò chơi cờ mang tính chiến lược cao giữa các chủng tộc.', '2025-03-17 00:42:31'),
(31, 11, 'Chương 5: Thách thức thần Tết', 'Sora và Shiro đối đầu với thần Tết trong một ván bài định mệnh.', '2025-03-17 00:42:31'),
(32, 12, 'Chương 1: Khiên hiệp sĩ bị phản bội', 'Naofumi bị phản bội ngay khi vừa đến thế giới mới.', '2025-03-17 00:42:31'),
(33, 12, 'Chương 2: Mua nô lệ', 'Naofumi quyết định mua Raphtalia để chiến đấu cùng mình.', '2025-03-17 00:42:31'),
(34, 12, 'Chương 3: Kỹ năng mới', 'Naofumi khám phá ra kỹ năng đặc biệt của chiếc khiên.', '2025-03-17 00:42:31'),
(35, 12, 'Chương 4: Chiến thắng đầu tiên', 'Naofumi và Raphtalia đánh bại một con quái vật mạnh.', '2025-03-17 00:42:31'),
(36, 12, 'Chương 5: Trận chiến với làn sóng quái vật', 'Naofumi đối mặt với thử thách đầu tiên - làn sóng quái vật.', '2025-03-17 00:42:31'),
(37, 13, 'Chương 1: Kazuma tái sinh', 'Kazuma chết và được hồi sinh trong một thế giới giả tưởng.', '2025-03-17 00:42:31'),
(38, 13, 'Chương 2: Gặp gỡ Aqua', 'Kazuma chọn Aqua làm đồng đội của mình.', '2025-03-17 00:42:31'),
(39, 13, 'Chương 3: Công việc đầu tiên', 'Kazuma và Aqua nhận nhiệm vụ đầu tiên.', '2025-03-17 00:42:31'),
(40, 13, 'Chương 4: Megumin xuất hiện', 'Kazuma gặp Megumin, một pháp sư cuồng nổ.', '2025-03-17 00:42:31'),
(41, 13, 'Chương 5: Đối mặt với quỷ vương', 'Kazuma và nhóm bạn đối đầu với một thuộc hạ của Quỷ Vương.', '2025-03-17 00:42:31'),
(42, 14, 'Chương 1: Tái sinh thành slime', 'Satoru Mikami tái sinh thành một con slime mạnh mẽ.', '2025-03-17 00:42:31'),
(43, 14, 'Chương 2: Gặp rồng Veldora', 'Rimuru gặp rồng Veldora và trở thành bạn của nó.', '2025-03-17 00:42:31'),
(44, 14, 'Chương 3: Thành lập quốc gia quái vật', 'Rimuru bắt đầu xây dựng một quốc gia cho các quái vật.', '2025-03-17 00:42:31'),
(45, 14, 'Chương 4: Đối đầu với Orc Lord', 'Rimuru chiến đấu với Orc Lord để bảo vệ đồng minh.', '2025-03-17 00:42:31'),
(46, 14, 'Chương 5: Liên minh với quỷ vương', 'Rimuru thiết lập liên minh với một trong những Quỷ Vương.', '2025-03-17 00:42:31'),
(47, 18, 'Chương 1', 'chương 1', '2025-03-17 01:22:14'),
(48, 18, 'Chương 2', 'Chương 3', '2025-03-17 01:22:23'),
(49, 19, 'Chương 1', 'Tacopi, người hành tinh Happy lên đường đi ban phát hạnh phúc cho toàn vũ trụ. Một ngày nọ, Tacopi được cô bé không cười Shizuka cứu đói khi đến Trái Đất, Tacopi quyết định sẽ giúp Shizuka cười thật nhiều. Liệu điều gì đang chờ hai người họ ở phía trước...?', '2025-03-17 09:56:02'),
(50, 19, 'Chương 2', '<p><b>xin chào</b></p>', '2025-03-17 11:16:58'),
(51, 19, 'chapter 3', '<ol><li><u><b>xin chào</b></u></li></ol>', '2025-03-17 11:17:29');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `novel_id` int NOT NULL,
  `content` text COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `user_id`, `novel_id`, `content`, `created_at`) VALUES
(17, 3, 8, 'ximn chào', '2025-03-17 00:44:15'),
(18, 3, 8, 'bộ này vjp', '2025-03-17 00:55:17'),
(19, 3, 9, 'Truyện hay nhưng mà hơi đắt', '2025-03-17 01:03:18'),
(20, 1, 9, 'Truyện này rẻ mà', '2025-03-17 01:04:21'),
(21, 9, 9, 'rẻ đâu má, tận 10 jack', '2025-03-17 01:15:49'),
(22, 9, 11, 'Main này hơi phế nhé', '2025-03-17 01:16:55'),
(23, 10, 8, 'GOTYYYYYY', '2025-03-17 01:28:13'),
(24, 10, 8, 'GOATTTTTTTTTTTTT', '2025-03-17 01:28:22'),
(25, 10, 10, 'xin chào', '2025-03-17 08:01:12');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int NOT NULL,
  `novel_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`user_id`, `novel_id`) VALUES
(3, 8),
(9, 8),
(10, 8),
(1, 9),
(3, 9),
(3, 10),
(9, 10),
(10, 12);

-- --------------------------------------------------------

--
-- Table structure for table `lightnovels`
--

CREATE TABLE `lightnovels` (
  `novel_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `author` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `description` text COLLATE utf8mb4_vietnamese_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `promo_id` int DEFAULT NULL,
  `status` enum('Đang tiến hành','Đã hoàn thành','Đã hủy bỏ') COLLATE utf8mb4_vietnamese_ci DEFAULT 'Đang tiến hành',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `lightnovels`
--

INSERT INTO `lightnovels` (`novel_id`, `title`, `author`, `description`, `cover_image`, `price`, `promo_id`, `status`, `created_at`) VALUES
(8, 'Monogatari Series', 'Nisio Isin', 'Một chuỗi câu chuyện siêu nhiên xoay quanh Araragi Koyomi và các hiện tượng kỳ lạ.', 'uploads/covers/67d7729a3d10e_MV5BMWE3OGFlNzktYzgwNy00OGEyLThmNDAtNjY5OWY5NGQzODliXkEyXkFqcGc@._V1_.jpg', 0.00, NULL, 'Đã hoàn thành', '2025-03-17 00:37:20'),
(9, 'Overlord', 'Kugane Maruyama', 'Một người chơi bị mắc kẹt trong một thế giới game và trở thành chúa tể undead.', 'uploads/covers/67d77286c185e_Overlord_poster.jpg', 5000000.00, NULL, 'Đang tiến hành', '2025-03-17 00:37:20'),
(10, 'Sword Art Online', 'Reki Kawahara', 'Những cuộc phiêu lưu trong thế giới thực tế ảo của Kirito.', 'uploads/covers/67d7726c245ef_MV5BN2NhYzU2NDEtYzI1NS00MjgzLThjZGUtOTYxNGJkZjZmNDdjXkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg', 50000.00, NULL, 'Đã hủy bỏ', '2025-03-17 00:37:20'),
(11, 'Re:Zero - Starting Life in Another World', 'Tappei Nagatsuki', 'Subaru Natsuki bị triệu hồi đến một thế giới khác và có khả năng quay lại thời gian sau khi chết.', 'uploads/covers/67d77254b7abf_MV5BNTY1M2NjMTItOGFhNi00NDU3LWExNzQtZGY2YWJlYzExNmU3XkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg', 0.00, NULL, 'Đã hủy bỏ', '2025-03-17 00:37:20'),
(12, 'No Game No Life', 'Yuu Kamiya', 'Hai anh em thiên tài game thủ bị triệu hồi đến một thế giới nơi tất cả được quyết định bằng trò chơi.', 'uploads/covers/67d7723d96f1b_unnamed.jpg', 500000.00, NULL, 'Đã hoàn thành', '2025-03-17 00:37:20'),
(13, 'The Rising of the Shield Hero', 'Aneko Yusagi', 'Naofumi Iwatani bị triệu hồi vào một thế giới khác và trở thành Khiên Hiệp Sĩ.', 'uploads/covers/67d7722c22e2e_images.jpg', 14000.00, NULL, 'Đang tiến hành', '2025-03-17 00:37:20'),
(14, 'Konosuba: God\'s Blessing on This Wonderful World!', 'Natsume Akatsuki', 'Một thanh niên Nhật Bản chết và được hồi sinh trong một thế giới giả tưởng đầy hài hước.', 'uploads/covers/67d7721da9aae_MV5BNTQ5NzJjMjgtNDliNC00YTdmLWJiOTQtYWRiMzY4OWU5NGQ3XkEyXkFqcGc@._V1_.jpg', 0.00, NULL, 'Đang tiến hành', '2025-03-17 00:37:20'),
(15, 'That Time I Got Reincarnated as a Slime', 'Fuse', 'Một người đàn ông bị giết và tái sinh thành một con slime mạnh mẽ.', 'uploads/covers/67d7720b15402_91544TYOd3L.jpg', 99000.00, NULL, 'Đã hoàn thành', '2025-03-17 00:37:20'),
(16, 'The Irregular at Magic High School', 'Tsutomu Satou', 'Một thế giới nơi phép thuật được sử dụng như công nghệ, và câu chuyện về Tatsuya Shiba.', 'uploads/covers/67d771d6ba5ac_MV5BYjBmOWY1ZjQtMTkwMi00MWE4LWE3MmMtOTdmNzA5OTQ5MjFkXkEyXkFqcGc@._V1_.jpg', 1000.00, NULL, 'Đang tiến hành', '2025-03-17 00:37:20'),
(18, 'Khóa chặt cửa nào Suzume', 'Shinkai Makoto', 'Khóa chặt cửa nào Suzume (Nhật: すずめの戸締まり Hepburn: Suzume no Tojimari?, n.đ.: Suzume khóa cửa) là một tác phẩm anime điện ảnh tiếng Nhật thuộc thể loại hành động viễn tưởng – hài kịch do Shinkai Makoto đạo diễn kiêm biên kịch', 'uploads/covers/67d778f6a9cb1_Suzume_no_Tojimari.tiff.jpg', 0.00, NULL, 'Đã hoàn thành', '2025-03-17 01:20:54'),
(19, 'Takopi\'s Original Sin', 'Đang Cập Nhật', 'Tacopi, người hành tinh Happy lên đường đi ban phát hạnh phúc cho toàn vũ trụ. Một ngày nọ, Tacopi được cô bé không cười Shizuka cứu đói khi đến Trái Đất, Tacopi quyết định sẽ giúp Shizuka cười thật nhiều. Liệu điều gì đang chờ hai người họ ở phía trước...?', 'uploads/covers/67d7f19b1cac5_Takopi\'s_Original_Sin_1.png', 100000.00, NULL, 'Đã hoàn thành', '2025-03-17 09:55:39');

-- --------------------------------------------------------

--
-- Table structure for table `novel_categories`
--

CREATE TABLE `novel_categories` (
  `novel_id` int NOT NULL,
  `category_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `novel_categories`
--

INSERT INTO `novel_categories` (`novel_id`, `category_id`) VALUES
(9, 1),
(10, 1),
(11, 1),
(13, 1),
(14, 1),
(15, 1),
(8, 2),
(9, 2),
(9, 3),
(10, 3),
(11, 3),
(12, 3),
(15, 3),
(16, 3),
(19, 3),
(11, 4),
(14, 4),
(8, 5),
(14, 5),
(15, 5),
(18, 5);

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `promo_id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL
) ;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`promo_id`, `code`, `discount_percentage`, `start_date`, `end_date`, `created_at`, `created_by`) VALUES
(1, 'SUMMER2024', 10.00, '2024-05-31 17:00:00', '2024-08-30 17:00:00', '2025-03-17 08:15:28', NULL),
(2, 'WINTER2024', 15.00, '2024-11-30 17:00:00', '2025-02-27 17:00:00', '2025-03-17 08:15:28', NULL),
(3, 'NEWYEAR2025', 20.00, '2024-12-31 17:00:00', '2025-01-14 17:00:00', '2025-03-17 08:15:28', NULL),
(4, 'BLACKFRIDAY', 30.00, '2024-11-24 17:00:00', '2024-11-29 17:00:00', '2025-03-17 08:15:28', NULL),
(5, 'SPRINGSALE', 25.00, '2025-02-28 17:00:00', '2025-03-31 17:00:00', '2025-03-17 08:15:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int NOT NULL,
  `user_id` int NOT NULL,
  `novel_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_applied` decimal(10,2) DEFAULT '0.00',
  `purchase_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','completed','cancelled') COLLATE utf8mb4_vietnamese_ci DEFAULT 'completed',
  `payment_method` varchar(50) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `user_id`, `novel_id`, `price`, `discount_applied`, `purchase_date`, `status`, `payment_method`) VALUES
(14, 1, 10, 50000.00, 0.00, '2025-03-17 01:03:56', 'completed', NULL),
(15, 1, 9, 5000000.00, 0.00, '2025-03-17 01:04:12', 'completed', NULL),
(16, 9, 10, 50000.00, 0.00, '2025-03-17 01:15:07', 'completed', NULL),
(17, 9, 9, 5000000.00, 0.00, '2025-03-17 01:15:22', 'completed', NULL),
(18, 9, 12, 500000.00, 0.00, '2025-03-17 01:16:16', 'completed', NULL),
(19, 10, 10, 50000.00, 0.00, '2025-03-17 08:01:35', 'completed', NULL),
(20, 10, 12, 500000.00, 0.00, '2025-03-17 08:04:28', 'completed', NULL),
(21, 10, 9, 5000000.00, 0.00, '2025-03-17 08:07:31', 'completed', NULL),
(22, 2, 10, 50000.00, 12500.00, '2025-03-17 08:44:23', 'completed', NULL),
(23, 2, 15, 99000.00, 0.00, '2025-03-17 09:53:42', 'completed', NULL),
(24, 2, 16, 1000.00, 250.00, '2025-03-17 10:31:47', 'completed', NULL),
(25, 2, 19, 100000.00, 25000.00, '2025-03-17 10:41:22', 'completed', NULL),
(26, 3, 12, 500000.00, 125000.00, '2025-03-17 11:49:43', 'completed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reading_history`
--

CREATE TABLE `reading_history` (
  `user_id` int NOT NULL,
  `novel_id` int NOT NULL,
  `chapter_id` int DEFAULT NULL,
  `last_read` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `reading_history`
--

INSERT INTO `reading_history` (`user_id`, `novel_id`, `chapter_id`, `last_read`) VALUES
(1, 14, 46, '2025-03-17 01:08:47'),
(2, 10, 22, '2025-03-17 08:21:30'),
(2, 13, 37, '2025-03-17 08:30:12'),
(2, 19, 49, '2025-03-17 10:37:09'),
(3, 8, 14, '2025-03-17 11:52:24'),
(3, 9, 21, '2025-03-17 00:43:31'),
(3, 10, 25, '2025-03-17 11:52:18'),
(3, 12, 32, '2025-03-17 11:52:07'),
(3, 18, 47, '2025-03-17 01:22:26'),
(9, 8, 14, '2025-03-17 01:17:52'),
(9, 10, 26, '2025-03-17 01:16:05'),
(9, 12, 34, '2025-03-17 01:16:21'),
(10, 8, 12, '2025-03-17 01:28:39'),
(10, 10, 23, '2025-03-17 08:01:37'),
(10, 12, 35, '2025-03-17 08:09:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_vietnamese_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `avatar_url`, `role`, `created_at`) VALUES
(1, 'u1', '1', 'user1@example.com', '/uploads/avatars/67d775f3ee44e.jpg', 'user', '2025-03-16 20:16:19'),
(2, 'user2', '2', 'user2@example.com', '/uploads/avatars/67d7ddb96cc81.jpg', 'admin', '2025-03-16 20:16:19'),
(3, 'ad1', '1', 'a@gmail.com', '/uploads/avatars/67d759f824ba8.png', 'admin', '2025-03-16 20:16:19'),
(6, 'u10', '$2y$10$9JFBt11JsAPEaIZD3GD7PusbLE92xlR3xGE2KL2kJgZV/PMtH1jnO', 'atarirachel@gmail.com', NULL, 'user', '2025-03-16 21:39:02'),
(7, 'Atari1', '$2y$10$695ZNuG/Rt6O9gOOuVqgDeKjEBvdJ6f5qz.LounAV1.7pVx81KGoq', '20221106@eaut.edu.vn', '/uploads/avatars/67d758fab91ea.jpg', 'user', '2025-03-16 22:53:41'),
(8, 'u2', '$2y$10$9MZUQr0T5/XgrAJSf0BUBuxNsIUypMFA9l5w9fEljBvIHH521rUxO', 'atarirachel111@gmail.com', NULL, 'user', '2025-03-17 01:10:01'),
(9, 'u3', '$2y$10$qgzLCwcGX2h0vVHpRdXezugBg5b.dsR7opJ6OF/bCu2g0UDfdgEme', 'atarirachel22@gmail.com', '/uploads/avatars/67d7777a3333a.jpg', 'user', '2025-03-17 01:14:26'),
(10, 'Atari10', '$2y$10$efXPxXLmjDHdBfUxEAIiKupnrPkAWJbZuzmjd1bt9L6rDs5thgbQS', 'atarirachel2131@gmail.com', '/uploads/avatars/67d77a9fc82d7.jpg', 'user', '2025-03-17 01:27:46'),
(11, 'user10', '$2y$10$7AJZNaWeLXulXIIbWc3YKuqlCj.3VY3oNDKP1WCAgzCWOFxVBh9Ii', 'atarirachel231@gmail.com', '/uploads/avatars/67d7da61efd61.jpg', 'admin', '2025-03-17 08:16:26'),
(12, 'Atari100', '$2y$10$kIy9.hyy49UZ9bj.ZJJ8cu/MqFeAGK97n4iOPSoR5E8uptrCQmdVC', 'a1111@gmail.com', 'uploads/avatars/67d8036473f3a_14f1dfdf80c414b237e6f2eefb652777.jpg', 'user', '2025-03-17 11:11:32');

-- --------------------------------------------------------

--
-- Table structure for table `user_promotions`
--

CREATE TABLE `user_promotions` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `promo_id` int DEFAULT NULL,
  `used` tinyint(1) DEFAULT '0',
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`chapter_id`),
  ADD KEY `novel_id` (`novel_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `novel_id` (`novel_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`novel_id`),
  ADD KEY `novel_id` (`novel_id`);

--
-- Indexes for table `lightnovels`
--
ALTER TABLE `lightnovels`
  ADD PRIMARY KEY (`novel_id`),
  ADD KEY `promo_id` (`promo_id`);

--
-- Indexes for table `novel_categories`
--
ALTER TABLE `novel_categories`
  ADD PRIMARY KEY (`novel_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `novel_id` (`novel_id`);

--
-- Indexes for table `reading_history`
--
ALTER TABLE `reading_history`
  ADD PRIMARY KEY (`user_id`,`novel_id`),
  ADD KEY `novel_id` (`novel_id`),
  ADD KEY `chapter_id` (`chapter_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_promotions`
--
ALTER TABLE `user_promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `promo_id` (`promo_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `chapter_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `lightnovels`
--
ALTER TABLE `lightnovels`
  MODIFY `novel_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promo_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_promotions`
--
ALTER TABLE `user_promotions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`novel_id`) REFERENCES `lightnovels` (`novel_id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`novel_id`) REFERENCES `lightnovels` (`novel_id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`novel_id`) REFERENCES `lightnovels` (`novel_id`) ON DELETE CASCADE;

--
-- Constraints for table `lightnovels`
--
ALTER TABLE `lightnovels`
  ADD CONSTRAINT `lightnovels_ibfk_1` FOREIGN KEY (`promo_id`) REFERENCES `promotions` (`promo_id`) ON DELETE SET NULL;

--
-- Constraints for table `novel_categories`
--
ALTER TABLE `novel_categories`
  ADD CONSTRAINT `novel_categories_ibfk_1` FOREIGN KEY (`novel_id`) REFERENCES `lightnovels` (`novel_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `novel_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`novel_id`) REFERENCES `lightnovels` (`novel_id`) ON DELETE CASCADE;

--
-- Constraints for table `reading_history`
--
ALTER TABLE `reading_history`
  ADD CONSTRAINT `reading_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reading_history_ibfk_2` FOREIGN KEY (`novel_id`) REFERENCES `lightnovels` (`novel_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reading_history_ibfk_3` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`chapter_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_promotions`
--
ALTER TABLE `user_promotions`
  ADD CONSTRAINT `user_promotions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_promotions_ibfk_2` FOREIGN KEY (`promo_id`) REFERENCES `promotions` (`promo_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
