-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 08:29 AM
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
-- Database: `lgn_elearning`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'bi-folder',
  `color` varchar(20) DEFAULT '#4f46e5',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `is_active`, `created_at`) VALUES
(1, 'Web Development', 'web-development', 'Pelajari pengembangan website dari dasar hingga mahir', 'bi-code-slash', '#3b82f6', 1, '2025-12-08 04:39:04'),
(2, 'Mobile Development', 'mobile-development', 'Buat aplikasi mobile untuk Android dan iOS', 'bi-phone', '#10b981', 1, '2025-12-08 04:39:04'),
(3, 'Data Science', 'data-science', 'Analisis data dan machine learning', 'bi-graph-up', '#8b5cf6', 1, '2025-12-08 04:39:04'),
(4, 'UI/UX Design', 'ui-ux-design', 'Desain antarmuka dan pengalaman pengguna', 'bi-palette', '#f59e0b', 1, '2025-12-08 04:39:04'),
(5, 'Digital Marketing', 'digital-marketing', 'Strategi pemasaran digital yang efektif', 'bi-megaphone', '#ef4444', 1, '2025-12-08 04:39:04'),
(6, 'Business', 'business', 'Keterampilan bisnis dan entrepreneurship', 'bi-briefcase', '#6366f1', 1, '2025-12-08 04:39:04'),
(7, 'Cloud Computing', 'cloud-computing', 'AWS, Google Cloud, dan Azure', 'bi-cloud', '#06b6d4', 1, '2025-12-08 04:39:04'),
(8, 'Cyber Security', 'cyber-security', 'Keamanan sistem dan jaringan', 'bi-shield-lock', '#dc2626', 1, '2025-12-08 04:39:04');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `certificate_number` varchar(100) NOT NULL,
  `certificate_url` varchar(255) DEFAULT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `discount_value` decimal(12,2) NOT NULL,
  `min_purchase` decimal(12,2) DEFAULT 0.00,
  `max_discount` decimal(12,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `course_id` int(11) DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_purchase`, `max_discount`, `usage_limit`, `used_count`, `course_id`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(1, 'BELAJAR50', 'Diskon 50% untuk semua kursus', 'percentage', 50.00, 100000.00, NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-08 04:39:04'),
(2, 'HEMAT100K', 'Potongan Rp 100.000', 'fixed', 100000.00, 200000.00, NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-08 04:39:04');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `preview_video_id` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_price` decimal(12,2) DEFAULT NULL,
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `language` varchar(50) DEFAULT 'Indonesia',
  `requirements` text DEFAULT NULL,
  `what_you_learn` text DEFAULT NULL,
  `duration_hours` int(11) DEFAULT 0,
  `total_lessons` int(11) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `tutor_id`, `category_id`, `title`, `slug`, `short_description`, `description`, `thumbnail`, `preview_video_id`, `price`, `discount_price`, `level`, `language`, `requirements`, `what_you_learn`, `duration_hours`, `total_lessons`, `is_published`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Complete Web Development Bootcamp 2024', 'complete-web-development-bootcamp-2024', 'Pelajari HTML, CSS, JavaScript, PHP, MySQL dan bangun 10+ proyek nyata', 'Kursus web development terlengkap yang akan membawa Anda dari pemula hingga siap kerja. Anda akan mempelajari teknologi terkini yang digunakan oleh perusahaan-perusahaan top di Indonesia.', 'https://www.elegantthemes.com/blog/wp-content/uploads/2018/12/top11.png', NULL, 1500000.00, 0.00, 'beginner', 'Indonesia', 'Komputer/Laptop|Koneksi internet|Tidak perlu pengalaman programming', 'Membangun website dari nol|Menguasai HTML, CSS, JavaScript|Database MySQL|Backend dengan PHP|Responsive design', 40, 120, 1, 1, '2025-12-08 04:39:04', '2025-12-08 07:02:54'),
(2, 2, 1, 'React.js Masterclass: Build Modern Web Apps', 'reactjs-masterclass-modern-web-apps', 'Kuasai React.js dari dasar hingga advanced dengan real projects', 'Pelajari React.js secara mendalam dan bangun aplikasi web modern yang scalable.', 'https://cdn.hashnode.com/res/hashnode/image/upload/v1701790313011/69e5d0ba-14ea-4035-8b43-58da1ccb2a8d.png', NULL, 1200000.00, 399000.00, 'intermediate', 'Indonesia', 'Dasar JavaScript|Pemahaman HTML/CSS|Code editor', 'React Fundamentals|Hooks & Context API|Redux State Management|React Router|Testing dengan Jest', 25, 80, 1, 1, '2025-12-08 04:39:04', '2025-12-08 05:45:07'),
(3, 3, 4, 'UI/UX Design Mastery: From Beginner to Pro', 'ui-ux-design-mastery-beginner-to-pro', 'Jadi UI/UX Designer profesional dengan Figma dan design thinking', 'Kursus komprehensif untuk menjadi UI/UX Designer yang handal.', 'https://course-net.com/wp-content/uploads/2022/12/1-8.jpeg', NULL, 5000.00, 2000.00, 'beginner', 'Indonesia', 'Tidak perlu pengalaman design|Laptop dengan Figma', 'Design Thinking Process|User Research|Wireframing|Figma Mastery|Prototyping|Design System', 30, 90, 1, 1, '2025-12-08 04:39:04', '2025-12-08 06:47:10'),
(4, 4, 3, 'Python for Data Science & Machine Learning', 'python-data-science-machine-learning', 'Analisis data dan bangun model ML dengan Python, Pandas, dan Scikit-learn', 'Pelajari Data Science dari nol menggunakan Python.', 'https://caltechsites-prod-assets.resources.caltech.edu/scienceexchange/images/AI-vs-ML_b63MzqT_JUll2of.orig.2e16d0ba.fill-933x525-c100.jpg', NULL, 1800000.00, 599000.00, 'intermediate', 'Indonesia', 'Dasar matematika|Logika programming|Laptop dengan Python', 'Python Programming|Pandas & NumPy|Data Visualization|Machine Learning|Deep Learning Intro', 45, 150, 1, 0, '2025-12-08 04:39:04', '2025-12-08 05:48:05'),
(5, 2, 2, 'Flutter Mobile Development: iOS & Android', 'flutter-mobile-development-ios-android', 'Bangun aplikasi mobile cross-platform dengan Flutter dan Dart', 'Buat aplikasi mobile untuk iOS dan Android sekaligus dengan satu codebase.', 'https://cdn.prod.website-files.com/6100d0111a4ed76bc1b9fd54/62ba7b7f547a660f37c11826_flutter%201.png', NULL, 1400000.00, 449000.00, 'intermediate', 'Indonesia', 'Dasar programming|Laptop dengan Flutter SDK', 'Dart Programming|Flutter Widgets|State Management|API Integration|Firebase', 35, 100, 1, 0, '2025-12-08 04:39:04', '2025-12-08 05:48:36'),
(6, 2, 1, 'Belajar PHP untuk Pemula', 'belajar-php-untuk-pemula', 'Pelajari dasar-dasar PHP untuk web development', 'Kursus PHP dari dasar untuk pemula yang ingin belajar backend development.', 'https://bs-uploads.toptal.io/blackfish-uploads/components/blog_post_page/4085715/cover_image/retina_1708x683/0925-3D_Data_Visualization_with_Open_Source_Tools_A_Tutorial_Using_VTK_Dan_Newsletter-0a7d5944210766ea5745cce80218ad3a.png', NULL, 0.00, NULL, 'beginner', 'Indonesia', 'Komputer/Laptop|Text Editor|XAMPP', 'Dasar PHP|Variabel dan Tipe Data|Control Structure|Functions|Database MySQL', 10, 30, 1, 0, '2025-12-08 04:39:04', '2025-12-08 05:49:28'),
(7, 6, 6, 'Cara Aura Farming', 'cara-aura-farming', 'Sigma Rizz', 'Skibidi pap pap', 'assets/thumbnails/69367a1e2f72e_1765177886.png', NULL, 1000.00, NULL, 'advanced', 'Indonesia', 'Cowo/Aura minimal 1000/Rizzer', 'Cara dapat cewe', 1, 1, 1, 0, '2025-12-08 07:11:26', '2025-12-08 07:12:28');

-- --------------------------------------------------------

--
-- Table structure for table `course_sections`
--

CREATE TABLE `course_sections` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `order_number` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_sections`
--

INSERT INTO `course_sections` (`id`, `course_id`, `title`, `description`, `order_number`, `created_at`) VALUES
(1, 1, 'Pengenalan Web Development', NULL, 1, '2025-12-08 04:39:04'),
(2, 1, 'HTML5 Fundamentals', NULL, 2, '2025-12-08 04:39:04'),
(3, 1, 'CSS3 Styling', NULL, 3, '2025-12-08 04:39:04'),
(4, 1, 'JavaScript Basics', NULL, 4, '2025-12-08 04:39:04'),
(5, 1, 'Bootstrap Framework', NULL, 5, '2025-12-08 04:39:04'),
(6, 2, 'Pengenalan React.js', NULL, 1, '2025-12-08 04:39:04'),
(7, 2, 'React Components', NULL, 2, '2025-12-08 04:39:04'),
(8, 2, 'State dan Props', NULL, 3, '2025-12-08 04:39:04'),
(9, 7, 'Rizz Class', NULL, 1, '2025-12-08 07:11:38');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `certificate_issued` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `transaction_id`, `enrolled_at`, `completed_at`, `progress_percentage`, `last_accessed_at`, `certificate_issued`) VALUES
(1, 5, 1, NULL, '2025-12-08 04:39:04', NULL, 25.50, NULL, 0),
(2, 5, 6, NULL, '2025-12-08 04:39:04', NULL, 0.00, NULL, 0),
(3, 6, 1, NULL, '2025-12-08 07:02:57', NULL, 0.00, '2025-12-08 07:10:01', 0),
(4, 7, 1, NULL, '2025-12-08 07:17:11', NULL, 0.00, '2025-12-08 07:17:18', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content_type` enum('video','article','quiz') DEFAULT 'video',
  `google_drive_file_id` varchar(255) DEFAULT NULL,
  `google_drive_url` varchar(500) DEFAULT NULL,
  `article_content` longtext DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 0,
  `order_number` int(11) DEFAULT 0,
  `is_preview` tinyint(1) DEFAULT 0,
  `resources` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `section_id`, `title`, `description`, `content_type`, `google_drive_file_id`, `google_drive_url`, `article_content`, `duration_minutes`, `order_number`, `is_preview`, `resources`, `created_at`, `updated_at`) VALUES
(1, 1, 'Apa itu Web Development?', 'Pengenalan dunia web development dan peluang karir', 'video', '1UOJnQwh-xjRhrac9shucRDa0G4EwV7h0', 'https://drive.google.com/file/d/1UOJnQwh-xjRhrac9shucRDa0G4EwV7h0/view?usp=drive_link', NULL, 15, 1, 1, NULL, '2025-12-08 04:39:04', '2025-12-08 07:01:07'),
(2, 1, 'Tools yang Dibutuhkan', 'Install dan setup tools untuk web development', 'video', '1AfP4PdV3ZgAsEB4zMwx6FFBXWiVHuktg', 'https://drive.google.com/file/d/1AfP4PdV3ZgAsEB4zMwx6FFBXWiVHuktg/view?usp=drive_link', NULL, 20, 2, 1, NULL, '2025-12-08 04:39:04', '2025-12-08 07:02:07'),
(3, 1, 'Cara Kerja Website', 'Memahami client-server architecture', 'video', '1KO4o5YegGPRASembr9HwDQcDoKSCI0VB', 'https://drive.google.com/file/d/1KO4o5YegGPRASembr9HwDQcDoKSCI0VB/view?usp=drive_link', NULL, 18, 3, 0, NULL, '2025-12-08 04:39:04', '2025-12-08 07:06:21'),
(4, 2, 'Struktur Dasar HTML', 'Membuat dokumen HTML pertama', 'video', '1zQPfSm2t3XnrNmBWDDBWFPBpF7DeIw8H', 'https://drive.google.com/file/d/1zQPfSm2t3XnrNmBWDDBWFPBpF7DeIw8H/view?usp=drive_link', NULL, 25, 1, 0, NULL, '2025-12-08 04:39:04', '2025-12-08 07:06:57'),
(5, 2, 'HTML Tags & Elements', 'Mengenal berbagai tag HTML', 'video', '1yrBnl7ytuLaun5EHLfoeQzxuhUBKLSfz', 'https://drive.google.com/file/d/1yrBnl7ytuLaun5EHLfoeQzxuhUBKLSfz/view?usp=drive_link', NULL, 30, 2, 0, NULL, '2025-12-08 04:39:04', '2025-12-08 07:07:10'),
(6, 2, 'Forms & Input', 'Membuat form interaktif', 'video', '1hOqfs5BckbfTi3FlhQq4c_a1t2D3e8hs', 'https://drive.google.com/file/d/1hOqfs5BckbfTi3FlhQq4c_a1t2D3e8hs/view?usp=drive_link', NULL, 35, 3, 0, NULL, '2025-12-08 04:39:04', '2025-12-08 07:07:26'),
(7, 3, 'CSS Selectors', 'Memilih elemen untuk di-styling', 'video', '1sYdsB2JMTTylZmqBV4O6uiuic1Ox77Ec', 'https://drive.google.com/file/d/1sYdsB2JMTTylZmqBV4O6uiuic1Ox77Ec/view?usp=drive_link', NULL, 25, 1, 0, NULL, '2025-12-08 04:39:04', '2025-12-08 07:07:38'),
(8, 3, 'Box Model', 'Memahami margin, padding, border', 'video', '1vtcJRfCHvDfsRvNrhsfCZeLPXwZybDyE', 'https://drive.google.com/file/d/1vtcJRfCHvDfsRvNrhsfCZeLPXwZybDyE/view?usp=drive_link', NULL, 20, 2, 0, NULL, '2025-12-08 04:39:04', '2025-12-08 07:07:50'),
(9, 6, 'Apa itu React?', 'Pengenalan library React.js', 'video', '1hhKvjvMZy7X33oHEi_0WIIji5jFb5UzU', 'https://drive.google.com/file/d/1hhKvjvMZy7X33oHEi_0WIIji5jFb5UzU/view?usp=drive_link', NULL, 15, 1, 1, NULL, '2025-12-08 04:39:04', '2025-12-08 07:08:03'),
(10, 6, 'Setup Project React', 'Membuat project React pertama', 'video', '17oK88CTQ9Kn819afDGkiJ5gvjilZlq9e', 'https://drive.google.com/file/d/17oK88CTQ9Kn819afDGkiJ5gvjilZlq9e/view?usp=drive_link', NULL, 20, 2, 1, NULL, '2025-12-08 04:39:04', '2025-12-08 07:08:16'),
(11, 9, 'Halo', 'Hai', 'video', '1kxiurElDL9uCXYD_uHMxXJe9JHL7iSh2', NULL, NULL, 10, 1, 1, NULL, '2025-12-08 07:12:23', '2025-12-08 07:12:23');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `watch_time_seconds` int(11) DEFAULT 0,
  `last_position_seconds` int(11) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_progress`
--

INSERT INTO `lesson_progress` (`id`, `user_id`, `lesson_id`, `is_completed`, `watch_time_seconds`, `last_position_seconds`, `completed_at`, `updated_at`) VALUES
(1, 6, 1, 0, 2, 0, NULL, '2025-12-08 07:10:01'),
(2, 6, 2, 0, 0, 0, NULL, '2025-12-08 07:03:04'),
(4, 6, 3, 0, 2, 0, NULL, '2025-12-08 07:03:03'),
(7, 6, 4, 0, 0, 0, NULL, '2025-12-08 07:03:05'),
(9, 6, 5, 0, 1, 0, NULL, '2025-12-08 07:09:18'),
(11, 6, 6, 0, 79, 0, NULL, '2025-12-08 07:04:26'),
(18, 6, 7, 0, 41, 0, NULL, '2025-12-08 07:10:00'),
(22, 6, 11, 0, 0, 0, NULL, '2025-12-08 07:15:53'),
(23, 7, 11, 0, 0, 0, NULL, '2025-12-08 07:16:40'),
(24, 7, 1, 0, 5, 0, NULL, '2025-12-08 07:17:18');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `link`, `is_read`, `created_at`) VALUES
(1, 6, 'Selamat Datang di LGN! 🎉', 'Terima kasih telah bergabung. Mulai jelajahi ribuan kursus berkualitas untuk mengembangkan skill Anda.', 'success', '/pages/courses.php', 0, '2025-12-08 04:41:11'),
(2, 6, 'Pendaftaran Berhasil! 🎉', 'Anda berhasil mendaftar di kursus \"Complete Web Development Bootcamp 2024\". Selamat belajar!', 'success', '/pages/learn.php?course=1', 0, '2025-12-08 07:02:57'),
(3, 7, 'Selamat Datang di LGN! 🎉', 'Terima kasih telah bergabung. Mulai jelajahi ribuan kursus berkualitas untuk mengembangkan skill Anda.', 'success', '/pages/courses.php', 0, '2025-12-08 07:16:20'),
(4, 7, 'Pendaftaran Berhasil! 🎉', 'Anda berhasil mendaftar di kursus \"Complete Web Development Bootcamp 2024\". Selamat belajar!', 'success', '/pages/learn.php?course=1', 0, '2025-12-08 07:17:11');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(50) DEFAULT 'text',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `value`, `type`, `updated_at`) VALUES
(1, 'site_name', 'LGN - Learn GeNius', 'text', '2025-12-08 04:39:04'),
(2, 'site_description', 'Platform e-learning terbaik di Indonesia', 'text', '2025-12-08 04:39:04'),
(3, 'contact_email', 'support@lgn.com', 'text', '2025-12-08 04:39:04'),
(4, 'contact_phone', '+62 812 3456 7890', 'text', '2025-12-08 04:39:04');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `final_amount` decimal(12,2) NOT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_code` varchar(100) DEFAULT NULL,
  `transaction_status` enum('pending','success','failed','expired','cancel','refund') DEFAULT 'pending',
  `midtrans_transaction_id` varchar(100) DEFAULT NULL,
  `midtrans_order_id` varchar(100) DEFAULT NULL,
  `midtrans_status_code` varchar(10) DEFAULT NULL,
  `midtrans_status_message` varchar(255) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `snap_redirect_url` varchar(500) DEFAULT NULL,
  `payment_response` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `course_id`, `order_id`, `amount`, `discount_amount`, `final_amount`, `payment_type`, `payment_code`, `transaction_status`, `midtrans_transaction_id`, `midtrans_order_id`, `midtrans_status_code`, `midtrans_status_message`, `snap_token`, `snap_redirect_url`, `payment_response`, `paid_at`, `expired_at`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 'LGN-20251208-1BC6A61D', 499000.00, 0.00, 499000.00, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, 'b82f985f-45b9-4f0d-af43-500b780cbabd', 'https://app.sandbox.midtrans.com/snap/v4/redirection/b82f985f-45b9-4f0d-af43-500b780cbabd', NULL, NULL, '2025-12-09 06:35:40', '2025-12-08 06:35:40', '2025-12-08 06:35:40'),
(2, 6, 2, 'LGN-20251208-411E86AB', 399000.00, 0.00, 399000.00, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, '9eeb12af-53de-41ae-8c07-15251a876b7b', 'https://app.sandbox.midtrans.com/snap/v4/redirection/9eeb12af-53de-41ae-8c07-15251a876b7b', NULL, NULL, '2025-12-09 06:45:38', '2025-12-08 06:45:38', '2025-12-08 06:45:38'),
(3, 6, 3, 'LGN-20251208-474296DB', 2000.00, 0.00, 2000.00, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, '79884d20-b567-499f-ae3a-0b3286571615', 'https://app.sandbox.midtrans.com/snap/v4/redirection/79884d20-b567-499f-ae3a-0b3286571615', NULL, NULL, '2025-12-09 06:47:16', '2025-12-08 06:47:16', '2025-12-08 06:47:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','tutor','admin') DEFAULT 'student',
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `bio`, `phone`, `is_active`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin LGN', 'admin@lgn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 'Administrator Learn GeNius', NULL, 1, NULL, '2025-12-08 04:39:04', '2025-12-08 04:39:04'),
(2, 'Budi Santoso', 'budi@lgn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tutor', NULL, 'Senior Full Stack Developer dengan pengalaman 10+ tahun di industri teknologi.', NULL, 1, NULL, '2025-12-08 04:39:04', '2025-12-08 04:39:04'),
(3, 'Siti Rahayu', 'siti@lgn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tutor', NULL, 'UI/UX Designer dan Product Designer berpengalaman.', NULL, 1, NULL, '2025-12-08 04:39:04', '2025-12-08 04:39:04'),
(4, 'Ahmad Wijaya', 'ahmad@lgn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tutor', NULL, 'Data Scientist dan Machine Learning Engineer.', NULL, 1, NULL, '2025-12-08 04:39:04', '2025-12-08 04:39:04'),
(5, 'Student Demo', 'student@lgn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NULL, NULL, NULL, 1, NULL, '2025-12-08 04:39:04', '2025-12-08 04:39:04'),
(6, 'Joni Papa', 'jonipapa@gmail.com', '$2y$10$2Pe/9krnEwiwAW/uBdLf/.MDGWaI98IKKG8fnE1w3PO72SZsU16Pa', 'tutor', NULL, NULL, NULL, 1, NULL, '2025-12-08 04:41:11', '2025-12-08 04:41:11'),
(7, 'Popo', 'popo@gmail.com', '$2y$10$qEf0gzohltY8kmIi0QyhO.UGgbuIj7PAFey2CtKLgJVo61UlrmoAi', 'student', NULL, NULL, NULL, 1, NULL, '2025-12-08 07:16:20', '2025-12-08 07:16:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `enrollment_id` (`enrollment_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `course_sections`
--
ALTER TABLE `course_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_progress` (`user_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `course_sections`
--
ALTER TABLE `course_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_3` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_sections`
--
ALTER TABLE `course_sections`
  ADD CONSTRAINT `course_sections_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `lesson_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE TABLE  `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `code` varchar(255) NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Clean up expired records periodically
-- You can run this as a cron job
-- DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1;
