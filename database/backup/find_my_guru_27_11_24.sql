-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2024 at 01:04 PM
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
-- Database: `find_my_guru`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_search_table`
--

CREATE TABLE `admin_search_table` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `searched_skill` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `city_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `name`, `city_id`, `created_at`, `updated_at`) VALUES
(1, 'Salt Lake', 1, '2024-11-12 12:29:20', '2024-11-12 12:29:20'),
(2, 'Newtown', 1, '2024-11-12 12:43:39', '2024-11-12 12:43:39');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `picture` varchar(255) NOT NULL,
  `short_content` text DEFAULT NULL,
  `full_content` longtext DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `mete_title` varchar(255) DEFAULT NULL,
  `meta_tag` text DEFAULT NULL,
  `meta_description` longtext DEFAULT NULL,
  `meta_keyword` text DEFAULT NULL,
  `is_trending` enum('0','1') NOT NULL DEFAULT '0',
  `seo1` text DEFAULT NULL,
  `seo2` text DEFAULT NULL,
  `seo3` text DEFAULT NULL,
  `status` enum('Published','Draft','Archived') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `category_id`, `title`, `category`, `picture`, `short_content`, `full_content`, `tags`, `mete_title`, `meta_tag`, `meta_description`, `meta_keyword`, `is_trending`, `seo1`, `seo2`, `seo3`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Test blog update', 'Technology', 'uploads/blog/17321087556716_academics.png', 'short_content', 'full_content2222222222222222', 'IT,Technology', 'meta title', 'meta tag', 'meta description', NULL, '1', 'seo 1', 'seo 2', 'seo 3', 'Published', '2024-10-03 10:45:07', '2024-11-21 05:54:00'),
(2, 1, 'Laravel is an MVC based PHP framework. In MVC architecture, ‘M’ stands for ‘Model’. A Model is basically a way for querying data to and from the table in the database.', 'Technology', 'uploads/webinar/17277771652299_fundoo.jpg', 'Laravel is an MVC based PHP framework. In MVC architecture, ‘M’ stands for ‘Model’. A Model is basically a way for querying data to and from the table in the database.', 'Laravel is an MVC based PHP framework. In MVC architecture, ‘M’ stands for ‘Model’. A Model is basically a way for querying data to and from the table in the database. Laravel provides a simple way to do that using Eloquent ORM (Object-Relational Mapping). Every table has a Model to interact with the table.\r\n\r\n', 'PHP', NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, '2024-10-09 10:55:29', '2024-10-09 10:55:29');

-- --------------------------------------------------------

--
-- Table structure for table `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `comment` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_comments`
--

INSERT INTO `blog_comments` (`id`, `blog_id`, `comment`, `created_at`, `updated_at`) VALUES
(1, 1, 'In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.', '2024-10-03 05:12:43', '2024-10-03 05:12:43');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_logo` varchar(255) DEFAULT NULL,
  `is_top_category` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `category_logo`, `is_top_category`, `status`, `created_at`, `updated_at`) VALUES
(1, 'IT-Service', NULL, 1, 1, NULL, '2024-11-13 11:47:00'),
(2, 'Academics', 'uploads/categories/17314988867800_academics.png', 1, 1, '2024-11-13 10:00:16', '2024-11-13 11:54:46');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `country_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `country_id`, `created_at`, `updated_at`) VALUES
(1, 'Kolkata', 1, NULL, NULL),
(2, 'Mumbai', 1, '2024-10-04 08:25:41', '2024-10-04 08:25:41');

-- --------------------------------------------------------

--
-- Table structure for table `cms_content`
--

CREATE TABLE `cms_content` (
  `id` int(11) NOT NULL,
  `content_type` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cms_content`
--

INSERT INTO `cms_content` (`id`, `content_type`, `content`, `description`, `created_at`, `updated_at`) VALUES
(1, 'aboutUs', 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature ', 'Where does it come from?\r\nContrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.\r\n\r\nThe standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \"de Finibus Bonorum et Malorum\" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.', '2024-10-04 12:09:11', '2024-10-04 12:09:11'),
(2, 'investorConnect', 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form,', 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.\r\n\r\nThe standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \"de Finibus Bonorum et Malorum\" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.', '2024-10-04 12:14:51', '2024-10-04 12:14:51'),
(3, 'privacyPolicy', 'Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. ', 'orem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '2024-10-04 12:20:09', '2024-10-04 12:20:09');

-- --------------------------------------------------------

--
-- Table structure for table `coin_packages_plans`
--

CREATE TABLE `coin_packages_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `duration_in_months` int(11) DEFAULT NULL,
  `min_amount` decimal(10,2) DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL,
  `coin_to_rupee_ratio` decimal(10,2) DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `bonus_coins` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coin_packages_plans`
--

INSERT INTO `coin_packages_plans` (`id`, `title`, `description`, `duration_in_months`, `min_amount`, `max_amount`, `coin_to_rupee_ratio`, `expiry_date`, `bonus_coins`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Guru\'s Regular', 'Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries,', 3, 100.00, 500.00, 0.50, '2024-10-18 18:51:50', 0, 'Active', '2024-10-04 13:21:50', '2024-10-04 13:21:50'),
(3, 'Guru\'s Premium', 'description', NULL, 3001.00, 5001.00, 1.50, '2024-12-18 00:00:00', 50, 'Inactive', '2024-11-14 08:21:21', '2024-11-20 13:54:44');

-- --------------------------------------------------------

--
-- Table structure for table `contact_us_page`
--

CREATE TABLE `contact_us_page` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `subtitle_1` text DEFAULT NULL,
  `subtitle_2` text DEFAULT NULL,
  `subtitle_3` text DEFAULT NULL,
  `subtitle_4` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address_1` text DEFAULT NULL,
  `address_2` text DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `insta_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `social_url_1` varchar(255) DEFAULT NULL,
  `social_url_2` varchar(255) DEFAULT NULL,
  `social_url_3` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_us_page`
--

INSERT INTO `contact_us_page` (`id`, `title`, `subtitle_1`, `subtitle_2`, `subtitle_3`, `subtitle_4`, `phone`, `email`, `address_1`, `address_2`, `facebook_url`, `insta_url`, `twitter_url`, `social_url_1`, `social_url_2`, `social_url_3`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Contact Us', 'We are always happy to assists you', 'Number', 'Email Address', NULL, '+91987654321', 'support@findmyguru.com', 'address_1', 'address_2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-21 13:23:08', '2024-11-21 14:03:20');

-- --------------------------------------------------------

--
-- Table structure for table `country`
--

CREATE TABLE `country` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `calling_code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `country`
--

INSERT INTO `country` (`id`, `name`, `calling_code`, `created_at`, `updated_at`) VALUES
(1, 'India', '+91', '0000-00-00 00:00:00', '2024-11-21 05:55:10');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `skill_id` bigint(20) UNSIGNED NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `year_of_exp` varchar(255) NOT NULL,
  `duration_value` int(11) NOT NULL,
  `duration_unit` enum('hours','days','weeks','months','years') NOT NULL,
  `batch_type` enum('Weekday','Weekend','Both') NOT NULL,
  `teaching_mode` enum('Online','Offline','Both') NOT NULL,
  `fee` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `fee_upon_enquiry` tinyint(1) NOT NULL DEFAULT 0,
  `first_class_free` tinyint(1) NOT NULL DEFAULT 0,
  `demo_video_url` varchar(255) DEFAULT NULL,
  `course_content` longtext DEFAULT NULL,
  `course_logo` varchar(255) DEFAULT NULL,
  `mete_title` varchar(255) DEFAULT NULL,
  `meta_description` longtext DEFAULT NULL,
  `search_tag` text DEFAULT NULL,
  `meta_keyword` text DEFAULT NULL,
  `seo1` text DEFAULT NULL,
  `seo2` text DEFAULT NULL,
  `seo3` text DEFAULT NULL,
  `status` enum('Approved','Rejected') NOT NULL DEFAULT 'Rejected',
  `top_tranding_course` tinyint(1) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `feature_field1` varchar(255) DEFAULT NULL,
  `feature_field2` varchar(255) DEFAULT NULL,
  `feature_field3` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `user_id`, `category_id`, `skill_id`, `course_name`, `year_of_exp`, `duration_value`, `duration_unit`, `batch_type`, `teaching_mode`, `fee`, `location`, `currency_id`, `fee_upon_enquiry`, `first_class_free`, `demo_video_url`, `course_content`, `course_logo`, `mete_title`, `meta_description`, `search_tag`, `meta_keyword`, `seo1`, `seo2`, `seo3`, `status`, `top_tranding_course`, `featured`, `feature_field1`, `feature_field2`, `feature_field3`, `created_at`, `updated_at`) VALUES
(2, 1, 1, 1, 'Node js', '5', 5, 'hours', 'Weekday', 'Online', 1230.00, NULL, 1, 0, 0, 'url edited', 'content', 'uploads/17317368524345_academics.png', 'meta title', 'meta description', 'tags', 'meta keyword', 'seo 1', NULL, NULL, 'Approved', 1, 0, NULL, NULL, NULL, '2024-09-28 04:30:37', '2024-11-16 06:00:52'),
(3, 2, 1, 1, 'PHP', '5', 5, 'hours', 'Weekday', 'Online', 1230.00, NULL, 1, 0, 0, 'url', 'content', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rejected', 0, 0, NULL, NULL, NULL, '2024-10-09 00:39:44', '2024-10-09 00:39:44'),
(4, 2, 1, 1, 'Node js', '5', 5, 'hours', 'Weekday', 'Online', 1230.00, NULL, 1, 0, 0, 'url', 'content', NULL, 'meta title', 'meta description', 'tags', 'meta keyword', 'seo 1', '', '', 'Approved', 1, 0, NULL, NULL, NULL, '2024-11-15 11:30:13', '2024-11-15 11:30:13'),
(5, 2, 1, 1, 'Test course11111111111111', '5', 5, 'hours', 'Weekday', 'Online', 1230.00, NULL, 1, 0, 0, 'url edited', 'content', NULL, 'meta title', 'meta description', 'tags', 'meta keyword', 'seo 1', NULL, NULL, 'Approved', 1, 0, NULL, NULL, NULL, '2024-11-21 05:56:21', '2024-11-21 05:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `courses_skills`
--

CREATE TABLE `courses_skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `skill_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currency`
--

CREATE TABLE `currency` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` char(5) DEFAULT NULL,
  `symbol` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currency`
--

INSERT INTO `currency` (`id`, `code`, `symbol`, `created_at`, `updated_at`) VALUES
(1, 'INR', '₹', '2024-11-13 06:06:10', '2024-11-13 06:06:10'),
(2, 'USD', '$', '2024-11-13 06:27:41', '2024-11-13 06:27:41');

-- --------------------------------------------------------

--
-- Table structure for table `duration`
--

CREATE TABLE `duration` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit` enum('days','weeks','months','years') NOT NULL DEFAULT 'days',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `type` enum('tutor','student','institute') DEFAULT NULL,
  `question` text NOT NULL,
  `answer` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `type`, `question`, `answer`, `created_at`, `updated_at`) VALUES
(1, 'tutor', 'What is Lorem?', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '2024-10-04 12:29:03', '2024-11-27 08:22:06'),
(2, 'student', 'Why do we use it?', 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).', '2024-10-04 12:29:03', '2024-10-04 12:29:03'),
(3, 'institute', 'Where can I get some?', 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.', '2024-10-04 12:29:38', '2024-10-04 12:29:38');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'English', '2024-11-13 06:55:58', '2024-11-13 06:55:58'),
(2, 'Hindi', '2024-11-13 06:56:13', '2024-11-13 06:56:13'),
(3, 'Bengali', '2024-11-13 06:56:23', '2024-11-13 06:56:23');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_09_23_093957_update_user_table', 2),
(5, '2024_09_23_113600_create_personal_access_tokens_table', 3),
(6, '2019_12_14_000001_create_personal_access_tokens_table', 4),
(7, '2024_09_27_100943_create_country', 5),
(8, '2024_09_27_102435_create_city_area', 6),
(9, '2024_09_27_111037_user_qualification_skill_language_courses', 7),
(10, '2024_09_28_060344_course_add_location_skill_fields', 8);

-- --------------------------------------------------------

--
-- Table structure for table `page_home`
--

CREATE TABLE `page_home` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `search_title` text DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `side_image_1` varchar(255) DEFAULT NULL,
  `side_image_2` varchar(255) DEFAULT NULL,
  `side_image_3` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_home`
--

INSERT INTO `page_home` (`id`, `title`, `search_title`, `banner_image`, `side_image_1`, `side_image_2`, `side_image_3`, `created_at`, `updated_at`) VALUES
(1, 'Find the perfect tutor and institues for any skill.', 'Online or Offline', 'uploads/CMS/17326231144032_download.jpg', 'uploads/CMS/17326231149083_download (1).jpg', NULL, NULL, '2024-11-26 16:54:55', '2024-11-26 17:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `page_investor_connect`
--

CREATE TABLE `page_investor_connect` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `subtitle_1` text DEFAULT NULL,
  `subtitle_2` text DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `button_text` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_investor_connect`
--

INSERT INTO `page_investor_connect` (`id`, `title`, `subtitle_1`, `subtitle_2`, `banner_image`, `content`, `button_link`, `button_text`, `created_at`, `updated_at`) VALUES
(1, 'Investor Connect', 'Unlocking the power of connection for startup.', NULL, 'uploads/CMS/17322801801311_academics.png', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', NULL, NULL, '2024-11-22 17:42:03', '2024-11-22 18:26:20');

-- --------------------------------------------------------

--
-- Table structure for table `page_privacy_policy`
--

CREATE TABLE `page_privacy_policy` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `subtitle_1` text DEFAULT NULL,
  `subtitle_2` text DEFAULT NULL,
  `subtitle_3` text DEFAULT NULL,
  `subtitle_4` text DEFAULT NULL,
  `content_1` longtext DEFAULT NULL,
  `content_2` longtext DEFAULT NULL,
  `content_3` longtext DEFAULT NULL,
  `content_4` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_privacy_policy`
--

INSERT INTO `page_privacy_policy` (`id`, `title`, `subtitle_1`, `subtitle_2`, `subtitle_3`, `subtitle_4`, `content_1`, `content_2`, `content_3`, `content_4`, `created_at`, `updated_at`) VALUES
(1, 'Privacy Policy', 'Collected information', 'Usages of Collected information', 'Children`s Privacy', NULL, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', NULL, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', NULL, '2024-11-22 18:58:45', '2024-11-22 19:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `payment_id` varchar(150) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_type` enum('subscription','coin') DEFAULT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `coin_package_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `payment_id`, `user_id`, `payment_method`, `amount`, `status`, `transaction_id`, `payment_type`, `subscription_id`, `coin_package_id`, `created_at`, `updated_at`) VALUES
(1, 'sas2e23s3', 2, 'online', 500, 'success', '2323sdsds', 'subscription', 1, NULL, '2024-11-22 11:58:54', '2024-11-22 11:58:54');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(5, 'App\\Models\\User', 6, 'pwc@malinator.com', '1f82f8434aa50f6fce003b4ec584a97fac15253e650cad0dcff71c1905cce57b', '[\"*\"]', NULL, NULL, '2024-10-04 04:13:45', '2024-10-04 04:13:45'),
(7, 'App\\Models\\User', 2, 'pranab@scwebtech.com', '7e1ae7bf235950b0b1cb9706573d5ec7f6365787f1b4c843bff933c5160e7f7e', '[\"*\"]', '2024-10-09 02:13:05', NULL, '2024-10-07 01:58:01', '2024-10-09 02:13:05'),
(8, 'App\\Models\\User', 2, 'pranab@scwebtech.com', '4f501c71b0bec2e837665cd1e957dade7dd5ca24a85febf2f3f8562c5cee08ea', '[\"*\"]', '2024-11-14 13:28:56', NULL, '2024-11-14 13:27:46', '2024-11-14 13:28:56'),
(9, 'App\\Models\\User', 2, 'pranab@scwebtech.com', 'a4b7403e2b92b6a1e204056a6416bb345abb2fbe4ec089c7c594eed324296ed6', '[\"*\"]', '2024-11-27 11:56:34', NULL, '2024-11-21 10:48:18', '2024-11-27 11:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `potential_student_unlock_log`
--

CREATE TABLE `potential_student_unlock_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_course_student_lead_id` int(11) NOT NULL,
  `used_coins` int(11) DEFAULT NULL,
  `unlock_status` enum('0','1') NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `potential_student_unlock_log`
--

INSERT INTO `potential_student_unlock_log` (`id`, `user_id`, `user_course_student_lead_id`, `used_coins`, `unlock_status`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 30, '1', '2024-10-08 06:17:01', '2024-10-08 06:17:01');

-- --------------------------------------------------------

--
-- Table structure for table `qualifications`
--

CREATE TABLE `qualifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qualifications`
--

INSERT INTO `qualifications` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'B-tech', '2024-11-12 13:25:14', '2024-11-12 13:25:14'),
(2, 'B-com', '2024-11-12 13:26:34', '2024-11-12 13:26:34');

-- --------------------------------------------------------

--
-- Table structure for table `search_enquiry`
--

CREATE TABLE `search_enquiry` (
  `id` int(11) NOT NULL,
  `skill` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `search_enquiry`
--

INSERT INTO `search_enquiry` (`id`, `skill`, `location`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 'Laravel', 'kolkata', '127.0.0.1', '2024-11-14 17:06:34', '2024-11-14 17:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('BBljCpTybkFyUSt0HKaPhjZlKOASrLgzu8rhIcuh', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieGZ0SjJVbThDSWY5dTdxRlBHOWVBUThMdUtsRjBuR0tnUTNpN2xORCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1731323252),
('eAaJjSex7tsUCds6izSfvwSqfgsIKEh1zRxaaToa', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTEFEZXhCMXR1Y2hGM0xmbDVIZjAwekFyMVJTQlpWZWhLTnVXRGtTUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1727179152),
('h7TCgUwHbemN2K1b5hTgylh9DI2ijR4B9uvifsWV', NULL, '127.0.0.1', 'PostmanRuntime/7.42.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieVlJdWRiWGtucGRsNE1lZU1VWU9rbUZ1ZkhNODl5VGxhMFg1UERwMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1728387904),
('TC3eWTHvRoEV4yky7V9Kj49pboz3aU1FVO6f7VyK', NULL, '127.0.0.1', 'PostmanRuntime/7.42.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicExnaVVnazV6ZURKT1BtZEhlZkVDMG5QY0lRVm5FSmlXMTJ0cEVVaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1728294809),
('tuQsFFTQ89CINW7op6B3ng8LlkvNwsslZyiPBeel', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY0lDRlFMSUJucGltZVo2d2FVNFZ1dWZZeFc5WUpBR1ZPaFlIclpTOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zaWdudXAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1727089562),
('zSi6hzc9TObRvuj6GnHOkzbznIAd0vVtEZSWHkU6', NULL, '127.0.0.1', 'PostmanRuntime/7.42.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUjFlWFdCZ0VZS3E2WUFHMnVpQnJoT0ZGU0hTSzNKWGZMcHlqUG5XbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1727088072);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'default_youtube_url', 'https://youtube.com/channel/default', 'Default Youtube Channel URL', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(2, 'default_course_image', 'path/to/default_course_image.jpg', 'Default image for courses', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(3, 'default_trainer_image', 'path/to/default_trainer_image.jpg', 'Default image for trainers', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(4, 'total_trainers', '10', 'Total number of trainers', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(5, 'total_courses', '50', 'Total number of courses', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(6, 'total_webinars', '5', 'Total number of webinars', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(7, 'max_teachers_contacted', '20', 'Max number of teachers that can be contacted', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(8, 'youtube_channel_url', 'https://youtube.com/channel/xyz', 'YouTube Channel URL', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(9, 'facebook_url', 'https://facebook.com/page', 'Facebook Page URL', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(10, 'linkedin_url', 'https://linkedin.com/company/page', 'LinkedIn Page URL', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(11, 'instagram_url', 'https://instagram.com/profile', 'Instagram Profile URL', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(12, 'mobile_number', '+1234567890', 'Mobile contact number', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(13, 'phone_number', '+0987654321', 'Phone contact number', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(14, 'gst_percentage', '18', 'GST percentage', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(15, 'gst_number', 'ABC123XYZ456', 'GST number of FMG', '2024-09-30 11:15:41', '2024-09-30 11:15:41'),
(16, 'site_url', 'https://www.findmyguru.com', NULL, '2024-11-15 09:49:28', '2024-11-15 09:49:28'),
(17, 'expert_instructors', 'expert instructors value', NULL, '2024-11-15 09:49:28', '2024-11-15 09:49:28'),
(18, 'title', 'Find my guru title', NULL, '2024-11-15 09:52:58', '2024-11-15 09:52:58'),
(19, 'happy_students', 'happy_students', NULL, '2024-11-15 09:52:58', '2024-11-15 09:52:58'),
(20, 'creative_webinars', '50', NULL, '2024-11-15 09:59:00', '2024-11-15 09:59:00'),
(21, 'certificate', 'Certificate details', NULL, '2024-11-15 09:59:00', '2024-11-15 09:59:00'),
(22, 'header_logo', 'uploads/CMS/17326172201170_download (1).jpg', NULL, '2024-11-26 06:47:13', '2024-11-26 10:33:40'),
(23, 'footer_logo', 'uploads/CMS/17326172207671_download.jpg', NULL, '2024-11-26 06:47:13', '2024-11-26 10:33:40'),
(24, 'copy_right', 'All right are reserve', NULL, '2024-11-26 06:48:54', '2024-11-26 10:05:27'),
(25, 'quick_links', 'I am developing a project based on 3rd party api data. Now api documentation accepts keyword when i want to search something.', NULL, '2024-11-26 06:48:54', '2024-11-26 10:05:27'),
(26, 'support', 'support', NULL, '2024-11-26 06:52:34', '2024-11-26 06:52:34'),
(27, 'disclaimer', 'disclaimer', NULL, '2024-11-26 06:52:34', '2024-11-26 10:05:27'),
(28, 'short_description', 'from your idea of session variable, i implemented logic in my way in the app and it works great.', NULL, '2024-11-26 06:54:54', '2024-11-26 10:05:27');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` int(11) NOT NULL,
  `skill` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `category_id`, `skill`, `name`, `created_at`, `updated_at`) VALUES
(1, 0, NULL, 'PHP', NULL, NULL),
(2, 1, NULL, 'Laravelaaaaaaa', NULL, '2024-11-27 10:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `state`, `country_id`) VALUES
(1, 'West bengal', 1),
(3, 'Assam', 1),
(4, 'Bihar', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_course_reviews`
--

CREATE TABLE `student_course_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `student_phone` varchar(255) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `review` longtext DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `approval_status` enum('Approved','Rejected','Pending') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_course_reviews`
--

INSERT INTO `student_course_reviews` (`id`, `user_id`, `course_id`, `student_id`, `student_name`, `student_email`, `student_phone`, `rating`, `review`, `date`, `approval_status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Pranab', 'pranab@scwebtech.com', '1234567890', 4, 'good', '0000-00-00 00:00:00', 'Approved', '0000-00-00 00:00:00', '2024-11-11 12:34:57'),
(3, 2, 2, NULL, 'Ruju', 'riju@malinator.com', '1234567890', 4, 'In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.', NULL, 'Pending', '2024-10-03 04:43:26', '2024-11-11 12:35:57');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `duration_in_months` int(11) NOT NULL,
  `actual_price` decimal(10,2) DEFAULT NULL,
  `offer_price` decimal(10,2) DEFAULT NULL,
  `free_coins` int(11) DEFAULT NULL,
  `is_course_listing` tinyint(1) NOT NULL DEFAULT 0,
  `featured_listing` tinyint(1) NOT NULL DEFAULT 0,
  `description` longtext DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `title`, `duration_in_months`, `actual_price`, `offer_price`, `free_coins`, `is_course_listing`, `featured_listing`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Free Trial', 3, 1000.00, 500.00, 10, 1, 1, 'Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries,', 'Active', '2024-10-04 13:07:57', '2024-10-04 13:07:57'),
(3, 'Regular', 3, 2000.00, 100.00, 1000, 1, 1, NULL, 'Active', '2024-11-14 06:31:00', '2024-11-14 06:31:00');

-- --------------------------------------------------------

--
-- Table structure for table `time_units`
--

CREATE TABLE `time_units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `unit_abbr` varchar(50) NOT NULL,
  `unit_plural` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('admin','tutor','institute') DEFAULT NULL,
  `f_name` varchar(255) NOT NULL,
  `l_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `gst_no` varchar(50) DEFAULT NULL,
  `year_of_exp` int(11) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `status` enum('Approved','Reject') NOT NULL DEFAULT 'Reject',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `f_name`, `l_name`, `email`, `email_verified_at`, `phone`, `password`, `profile_pic`, `bio`, `country`, `state`, `city`, `area`, `address`, `gst_no`, `year_of_exp`, `remember_token`, `status`, `created_at`, `updated_at`) VALUES
(1, 'tutor', 'Test User', NULL, 'testuser@example.com', NULL, 1234567890, '$2y$12$Tn3JFvPsOFyJvvTBbIznTeec2k2uij7GD56W4viQlj7diAHc6aIoa', 'uploads/profile_pic/17276941046898_fundoo.jpg', NULL, '1', NULL, '1', NULL, NULL, NULL, NULL, NULL, 'Reject', '2024-09-23 06:36:40', '2024-09-30 05:31:44'),
(2, 'admin', 'Pranab', 'Bhadra', 'pranab@scwebtech.com', NULL, 1234567890, '$2y$12$Z1RNOpvX8w1ZT.Cw0BdczO8j4UUQmp5p6k2YQcjJS1GavZKhrrYrK', 'uploads/profile_pic/17317582451689_academics.png', 'bio', '1', '1', '1', '1', 'kolkata', NULL, 5, NULL, 'Reject', '2024-09-23 06:58:08', '2024-11-16 11:57:25'),
(3, 'tutor', 'Deb', 'Das', 'deb@malinator.com', NULL, 1234567890, '$2y$12$Z1RNOpvX8w1ZT.Cw0BdczO8j4UUQmp5p6k2YQcjJS1GavZKhrrYrK', NULL, NULL, '1', NULL, '1', NULL, NULL, NULL, NULL, NULL, 'Reject', '2024-10-08 10:09:49', '2024-10-08 10:09:49'),
(8, 'tutor', 'Moumita', 'Das', 'dev2.scwt@gmail.com', NULL, 1234567890, '$2y$12$Bg6XndPPsIsqMXLGgsmopOitRfQXdM.pXxycWWFYqh5CKKkr1BkEC', 'uploads/profile_pic/17320119118521_academics.png', 'bio', '1', '1', '1', NULL, 'kolkata', NULL, NULL, NULL, 'Approved', '2024-11-19 10:25:11', '2024-11-19 10:25:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_action`
--

CREATE TABLE `user_action` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `tutor_action` varchar(255) DEFAULT NULL,
  `tutor_action_description` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_coin_consumption_history`
--

CREATE TABLE `user_coin_consumption_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `skill_name` varchar(255) NOT NULL,
  `enquiry_date` datetime DEFAULT NULL,
  `coin_consumed_date` datetime DEFAULT NULL,
  `coins_consumed` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_coin_consumption_history`
--

INSERT INTO `user_coin_consumption_history` (`id`, `user_id`, `student_name`, `skill_name`, `enquiry_date`, `coin_consumed_date`, `coins_consumed`, `created_at`, `updated_at`) VALUES
(1, 2, 'Vivek', 'PHP', '2024-10-18 00:00:00', '2024-10-07 06:00:56', 50, '2024-10-07 00:30:56', '2024-10-07 00:30:56');

-- --------------------------------------------------------

--
-- Table structure for table `user_coin_purchase_history`
--

CREATE TABLE `user_coin_purchase_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `coin_package_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_date` datetime DEFAULT NULL,
  `coins_received` int(11) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('Success','Failed','Pending') DEFAULT NULL,
  `transuction_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_coin_purchase_history`
--

INSERT INTO `user_coin_purchase_history` (`id`, `user_id`, `coin_package_id`, `purchase_date`, `coins_received`, `amount_paid`, `payment_status`, `transuction_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2024-10-05 11:47:55', 250, 500.00, 'Success', 'sdqwexcasd', '2024-10-05 06:17:55', '2024-10-05 06:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `user_course_student_lead`
--

CREATE TABLE `user_course_student_lead` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `user_type` varchar(255) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `student_phone` varchar(255) DEFAULT NULL,
  `student_message` longtext DEFAULT NULL,
  `tutor_action` varchar(255) DEFAULT NULL,
  `tutor_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_course_student_lead`
--

INSERT INTO `user_course_student_lead` (`id`, `user_id`, `course_id`, `category_id`, `user_type`, `student_name`, `student_email`, `student_phone`, `student_message`, `tutor_action`, `tutor_notes`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 1, NULL, 'Ruju', 'riju@malinator.com', '1234567890', 'In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.', NULL, NULL, '2024-10-03 04:29:52', '2024-10-03 04:29:52'),
(2, 3, 3, 1, NULL, 'Vivek', 'vivek@malinator.com', '1234567890', 'In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.', NULL, NULL, '2024-10-04 00:42:02', '2024-10-04 00:42:02'),
(3, 3, 3, 1, NULL, 'Moumita', 'dev2.scwt@gmail.com', '1234567890', 'In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.', NULL, NULL, '2024-11-08 11:23:53', '2024-11-08 11:23:53'),
(4, 3, 3, 1, NULL, 'Moumita', 'dev2.scwt@gmail.com', '1234567890', 'In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without relying on meaningful content. Lorem ipsum may be used as a placeholder before the final copy is available.', NULL, NULL, '2024-11-08 11:34:53', '2024-11-08 11:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `user_languages`
--

CREATE TABLE `user_languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `language` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_languages`
--

INSERT INTO `user_languages` (`id`, `user_id`, `language`, `created_at`, `updated_at`) VALUES
(8, 2, 'BENGALI', '2024-11-16 11:57:25', '2024-11-16 11:57:25'),
(9, 2, 'ENGLISH', '2024-11-16 11:57:25', '2024-11-16 11:57:25'),
(10, 8, 'BENGALI', '2024-11-19 10:25:11', '2024-11-19 10:25:11'),
(11, 8, 'ENGLISH', '2024-11-19 10:25:11', '2024-11-19 10:25:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_qualifications`
--

CREATE TABLE `user_qualifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `qualification` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_qualifications`
--

INSERT INTO `user_qualifications` (`id`, `user_id`, `qualification`, `created_at`, `updated_at`) VALUES
(12, 2, 'B.Tech', '2024-11-16 11:57:25', '2024-11-16 11:57:25'),
(13, 2, 'M.Tech', '2024-11-16 11:57:25', '2024-11-16 11:57:25'),
(14, 8, 'B.Tech', '2024-11-19 10:25:11', '2024-11-19 10:25:11'),
(15, 8, 'M.Tech', '2024-11-19 10:25:11', '2024-11-19 10:25:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

CREATE TABLE `user_skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `skill` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_skills`
--

INSERT INTO `user_skills` (`id`, `user_id`, `skill`, `created_at`, `updated_at`) VALUES
(1, 1, 'PHP', '2024-10-04 08:19:51', '2024-10-04 08:19:51'),
(2, 1, 'Laravel', '2024-10-04 08:19:51', '2024-10-04 08:19:51'),
(15, 2, 'PHP', '2024-11-16 11:57:25', '2024-11-16 11:57:25'),
(16, 2, 'LARAVEL', '2024-11-16 11:57:25', '2024-11-16 11:57:25'),
(17, 2, 'NODE', '2024-11-16 11:57:25', '2024-11-16 11:57:25'),
(18, 2, 'EXPRESS', '2024-11-16 11:57:25', '2024-11-16 11:57:25'),
(19, 8, 'NODE', '2024-11-19 10:25:11', '2024-11-19 10:25:11'),
(20, 8, 'EXPRESS', '2024-11-19 10:25:11', '2024-11-19 10:25:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_subscription_purchase_history`
--

CREATE TABLE `user_subscription_purchase_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `subcription_id` bigint(20) UNSIGNED NOT NULL,
  `subcription_date` datetime DEFAULT NULL,
  `package_name` varchar(255) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `gst_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('Success','Failed','Pending') DEFAULT NULL,
  `transuction_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_subscription_purchase_history`
--

INSERT INTO `user_subscription_purchase_history` (`id`, `user_id`, `subcription_id`, `subcription_date`, `package_name`, `start_date`, `end_date`, `amount_paid`, `gst_amount`, `payment_status`, `transuction_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2024-10-18 00:00:00', 'package_name', '2024-10-18 00:00:00', '2024-12-18 00:00:00', 500.00, 10.00, 'Success', 'sdqwexcasd', '2024-10-04 08:48:40', '2024-10-04 08:48:40');

-- --------------------------------------------------------

--
-- Table structure for table `user_webinar_student_lead`
--

CREATE TABLE `user_webinar_student_lead` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `webinar_id` bigint(20) UNSIGNED NOT NULL,
  `webinar_title` varchar(255) DEFAULT NULL,
  `user_type` varchar(255) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `student_phone` varchar(255) DEFAULT NULL,
  `student_message` longtext DEFAULT NULL,
  `tutor_action` varchar(255) DEFAULT NULL,
  `tutor_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_webinar_student_lead`
--

INSERT INTO `user_webinar_student_lead` (`id`, `user_id`, `webinar_id`, `webinar_title`, `user_type`, `student_name`, `student_email`, `student_phone`, `student_message`, `tutor_action`, `tutor_notes`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Generative AI', NULL, 'Dipanjan', 'dipan@malinator.com', '1234567890', NULL, NULL, NULL, '2024-10-03 03:08:47', '2024-10-03 03:08:47');

-- --------------------------------------------------------

--
-- Table structure for table `webinars`
--

CREATE TABLE `webinars` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `language` varchar(255) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `no_of_seats` int(11) DEFAULT NULL,
  `delivery_mode` enum('Online','Offline','Both') DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `agenda` varchar(255) NOT NULL,
  `demo_video_url` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `mete_title` varchar(255) DEFAULT NULL,
  `meta_description` longtext DEFAULT NULL,
  `search_tag` text DEFAULT NULL,
  `meta_keyword` text DEFAULT NULL,
  `seo1` text DEFAULT NULL,
  `seo2` text DEFAULT NULL,
  `seo3` text DEFAULT NULL,
  `status` enum('Approved','Rejected') NOT NULL DEFAULT 'Rejected',
  `top_tranding_course` tinyint(1) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `feature_field1` varchar(255) DEFAULT NULL,
  `feature_field2` varchar(255) DEFAULT NULL,
  `feature_field3` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `webinars`
--

INSERT INTO `webinars` (`id`, `user_id`, `category_id`, `title`, `language`, `start_date`, `end_date`, `start_time`, `end_time`, `fee`, `currency_id`, `no_of_seats`, `delivery_mode`, `address`, `agenda`, `demo_video_url`, `logo`, `mete_title`, `meta_description`, `search_tag`, `meta_keyword`, `seo1`, `seo2`, `seo3`, `status`, `top_tranding_course`, `featured`, `feature_field1`, `feature_field2`, `feature_field3`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Generative AI', 'English', '2024-10-15', '2024-10-16', '12:00:00', '15:00:00', 5000.00, 1, 200, 'Online', 'Kolkata', 'About latest', NULL, 'uploads/webinar/17277641802249_fundoo.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rejected', 0, 0, NULL, NULL, NULL, '2024-10-01 00:59:40', '2024-10-01 02:05:00'),
(2, 2, 1, 'About web technology', 'English', '2024-11-15', '2024-11-16', '12:00:00', '15:00:00', 500.00, 1, 50, 'Online', 'Kolkata', 'About latest web development', NULL, 'uploads/webinar/17277771652299_fundoo.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rejected', 0, 0, NULL, NULL, NULL, '2024-10-01 04:36:05', '2024-10-01 04:36:05'),
(4, 2, 1, 'About web technology', 'English', '2024-11-15', '2024-11-16', '12:00:00', '15:00:00', 500.00, 1, 50, 'Online', 'Kolkata', 'About latest web development', 'demo_video_url', 'uploads/webinar/17321904831582_academics.png', 'mete_title', NULL, NULL, NULL, NULL, NULL, NULL, 'Approved', 0, 0, NULL, NULL, NULL, '2024-11-21 12:01:23', '2024-11-21 12:01:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_search_table`
--
ALTER TABLE `admin_search_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `areas_city_id_index` (`city_id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blogs_category_id_index` (`category_id`);

--
-- Indexes for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cities_country_id_index` (`country_id`);

--
-- Indexes for table `cms_content`
--
ALTER TABLE `cms_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coin_packages_plans`
--
ALTER TABLE `coin_packages_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_us_page`
--
ALTER TABLE `contact_us_page`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_user_id_index` (`user_id`),
  ADD KEY `courses_category_id_index` (`category_id`),
  ADD KEY `courses_currency_id_index` (`currency_id`),
  ADD KEY `courses_skill_id_index` (`skill_id`);

--
-- Indexes for table `courses_skills`
--
ALTER TABLE `courses_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_skills_course_id_index` (`course_id`);

--
-- Indexes for table `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `duration`
--
ALTER TABLE `duration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_home`
--
ALTER TABLE `page_home`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_investor_connect`
--
ALTER TABLE `page_investor_connect`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_privacy_policy`
--
ALTER TABLE `page_privacy_policy`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `potential_student_unlock_log`
--
ALTER TABLE `potential_student_unlock_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `search_enquiry`
--
ALTER TABLE `search_enquiry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_course_reviews`
--
ALTER TABLE `student_course_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_course_reviews_user_id_index` (`user_id`),
  ADD KEY `student_course_reviews_course_id_index` (`course_id`),
  ADD KEY `student_course_reviews_student_id_index` (`student_id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_units`
--
ALTER TABLE `time_units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_action`
--
ALTER TABLE `user_action`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_action_user_id_index` (`user_id`),
  ADD KEY `user_action_course_id_index` (`course_id`);

--
-- Indexes for table `user_coin_consumption_history`
--
ALTER TABLE `user_coin_consumption_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_coin_consumption_history_user_id_index` (`user_id`);

--
-- Indexes for table `user_coin_purchase_history`
--
ALTER TABLE `user_coin_purchase_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_coin_purchase_history_user_id_index` (`user_id`),
  ADD KEY `user_coin_purchase_history_coin_package_id_index` (`coin_package_id`);

--
-- Indexes for table `user_course_student_lead`
--
ALTER TABLE `user_course_student_lead`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_course_student_lead_user_id_index` (`user_id`),
  ADD KEY `user_course_student_lead_course_id_index` (`course_id`);

--
-- Indexes for table `user_languages`
--
ALTER TABLE `user_languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_languages_user_id_index` (`user_id`);

--
-- Indexes for table `user_qualifications`
--
ALTER TABLE `user_qualifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_qualifications_user_id_index` (`user_id`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_skills_user_id_index` (`user_id`);

--
-- Indexes for table `user_subscription_purchase_history`
--
ALTER TABLE `user_subscription_purchase_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_subscription_purchase_history_user_id_index` (`user_id`),
  ADD KEY `user_subscription_purchase_history_subcription_id_index` (`subcription_id`);

--
-- Indexes for table `user_webinar_student_lead`
--
ALTER TABLE `user_webinar_student_lead`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_webinar_student_lead_user_id_index` (`user_id`),
  ADD KEY `user_webinar_student_lead_webinar_id_index` (`webinar_id`);

--
-- Indexes for table `webinars`
--
ALTER TABLE `webinars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `webinars_user_id_index` (`user_id`),
  ADD KEY `webinars_category_id_index` (`category_id`),
  ADD KEY `webinars_currency_id_index` (`currency_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_search_table`
--
ALTER TABLE `admin_search_table`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cms_content`
--
ALTER TABLE `cms_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `coin_packages_plans`
--
ALTER TABLE `coin_packages_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_us_page`
--
ALTER TABLE `contact_us_page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `country`
--
ALTER TABLE `country`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `courses_skills`
--
ALTER TABLE `courses_skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currency`
--
ALTER TABLE `currency`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `duration`
--
ALTER TABLE `duration`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `page_home`
--
ALTER TABLE `page_home`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `page_investor_connect`
--
ALTER TABLE `page_investor_connect`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `page_privacy_policy`
--
ALTER TABLE `page_privacy_policy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `potential_student_unlock_log`
--
ALTER TABLE `potential_student_unlock_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `qualifications`
--
ALTER TABLE `qualifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `search_enquiry`
--
ALTER TABLE `search_enquiry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_course_reviews`
--
ALTER TABLE `student_course_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `time_units`
--
ALTER TABLE `time_units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_action`
--
ALTER TABLE `user_action`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_coin_consumption_history`
--
ALTER TABLE `user_coin_consumption_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_coin_purchase_history`
--
ALTER TABLE `user_coin_purchase_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_course_student_lead`
--
ALTER TABLE `user_course_student_lead`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_languages`
--
ALTER TABLE `user_languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_qualifications`
--
ALTER TABLE `user_qualifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_subscription_purchase_history`
--
ALTER TABLE `user_subscription_purchase_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_webinar_student_lead`
--
ALTER TABLE `user_webinar_student_lead`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `webinars`
--
ALTER TABLE `webinars`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
