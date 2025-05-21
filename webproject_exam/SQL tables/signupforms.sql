-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2024 at 04:48 AM
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
-- Database: `signupforms`
--

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `exam_number` varchar(255) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `subject_name`, `exam_number`, `start_datetime`, `end_datetime`, `is_active`, `created_at`) VALUES
(1, 'JAVA', '001', '2024-10-29 16:30:00', '2024-10-29 17:00:00', 1, '2024-10-29 10:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `question_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option4` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `correct_option` int(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `exam_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `question_text`, `option1`, `option2`, `option3`, `option4`, `correct_option`, `created_at`, `exam_id`) VALUES
(1, 'what is this?', 'abcd', 'efgh', 'ijhbd', 'dbdgb', 3, '2024-10-04 10:46:46', NULL),
(2, 'helloo?', 'qasd', '951', '753', 'hthth', 4, '2024-10-04 11:03:44', NULL),
(3, 'What is java?', 'java3221', 'dfhdh1', 'uyuk7uy', 'tyktykt', 1, '2024-10-29 10:38:21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration`
--

INSERT INTO `registration` (`id`, `username`, `password`) VALUES
(10, 'pooja001', '0203'),
(12, 'rudra17', 'r178'),
(13, 'dhruv52', 'd52'),
(14, 'Aryan12', '001'),
(15, 'pooja', '123');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `username`, `password`, `full_name`, `email`, `created_at`) VALUES
(4, 'dhruv01', '$2y$10$aRU5Jfw7D7jfo4Qj0rcYSukgbZ44a5HiRejboi0x0GZ3RdxS2E1.u', 'Dhruv Gohil', 'dhruv@gmail.com', '0000-00-00 00:00:00'),
(7, 'abcd', '123', 'John Doe', 'john.doe77@example.com', '2024-10-04 04:29:42'),
(9, 'teacher', '$2y$10$hN2rgsuqrzQF70.GLdim1.Sz.oK5WtP7Lt4xJhSbIj6F8BZI2oqDa', 'teacher', 'teacher@gmail.com', '2024-11-05 04:44:34'),
(10, 't01', '$2y$10$5YwJQ0KJrOo4MR8GWk07Me3vCjLMUJP7PAgrtO4OBnKAJbPlHRcGK', 't01', 't01@gmail.com', '2024-11-10 03:09:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `enrollment_id` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `mobile` int(15) NOT NULL,
  `course` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `teacher_comment` text DEFAULT NULL,
  `security_question1` varchar(255) DEFAULT NULL,
  `security_answer1` varchar(255) DEFAULT NULL,
  `security_question2` varchar(255) DEFAULT NULL,
  `security_answer2` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `enrollment_id`, `dob`, `gender`, `mobile`, `course`, `email`, `password`, `profile_photo`, `username`, `status`, `teacher_comment`, `security_question1`, `security_answer1`, `security_question2`, `security_answer2`) VALUES
(1, 'Vipul Patel', '23000180', '2005-01-01', 'Male', 2147483647, 'B.Sc', 'vipul23@gmail.com', '$2y$10$Y1PsbVdpwr6TDG1Tq941RuDIvDEJQyu/zzlF9fjLKpcqYuR.sPqki', '1236480.jpg', 'Vipul180', 'approved', '', NULL, NULL, NULL, NULL),
(2, 'Dhruv Gohil', '23000510', '2023-01-11', 'Male', 2147483647, 'B.Tech', 'dhruv321@gmail.com', '$2y$10$.zi5f0lYfsF2N5lhJxCNDOhYFDBxMQ.fPLm.wkchl8NCK3cEiyRZi', '1236480.jpg', 'Dhruv510', 'rejected', '', NULL, NULL, NULL, NULL),
(3, 'Aryan Jadhav', '23000545', '2024-09-07', 'Male', 2147483647, 'B.Tech', 'aryan123@gmail.com', '$2y$10$DnX7bSYP5dGkqnoMwvZnT.O.SvA5VD7OauL/Ve3rHEQzaEHKnK18y', '1236480.jpg', 'Aryan545', 'approved', '', 'What was the name of your first pet?', '$2y$10$QrrZDki8edd9zx8x8Q4tJ.jljHgTHmHG9PfZeNY3RO88WD7L/lBDO', 'What was the make of your first car?', '$2y$10$Nxt/55RAmKHk/DrsMi1uD.k/nDMqeyAv6m1L.PgCHA8tMkB/NQI1u'),
(4, 'rudra bhatt', '23000551', '2024-09-06', 'Male', 2147483647, 'M.Tech', 'rudra365@gmail.com', '$2y$10$dMtItqFE1nBelyC2fQ37NeYmjCGfw/lrpKRuEJCUVO6IWFC9m/rZG', '1236480.jpg', 'rudra551', 'approved', '', 'In what city were you born?', '$2y$10$DZV1CyvGa/MLCNt7qu.7vuxrzg/KRQjHIoPoo7S/S91.jT1Z7MLUO', 'What was the make of your first car?', '$2y$10$r0Kpy6OUBFFzU5n8Fo2kJ.F63zFsUwMZPSsSoeBrn1B6MwTJvO23G'),
(5, 'Saurav Rajput', '23000600', '2024-08-09', 'Male', 2147483647, 'B.Tech', 'saurav123@gmail.com', '$2y$10$u42A95NopjuYGg3ua9gPXOQGyVatjQc0XXMmQ9OSlq7jhwWbVim9O', '1236480.jpg', 'Saurav600', 'approved', '', 'In what city were you born?', '$2y$10$2vg9jkBwxX5YWqzcLgj9juCPzSYwzWljwe4mFNGumgcuMU.gjKbQ.', 'What is your favorite book?', '$2y$10$TmTZ22lOYQwkY4Jv.k9VOu5A88Y8uvplAhUK/9Yn7k2KRR7SMT/PS'),
(6, 'vishal', '5412335', '2024-09-06', 'Male', 2147483647, 'M.Tech', 'vishal@gmail.com', '$2y$10$CjmvJnBXT45z.w9CzBS2XeyVk5/peBWSCOmRM6q28E/sUmidcEUdG', '1236480.jpg', 'vishal335', 'approved', '', 'In what city were you born?', '$2y$10$FUUNpdT6inHCr9p0XuSeROGAImetJmuGRIsBet.x4DWyfJk0PSR6a', 'What is your favorite book?', '$2y$10$93/ifNXW6DbphK/1b7L1SOC7RsmG1PfWdo0zey9.NcWnB4pkTgcXe'),
(7, 'kunj patel', '23000700', '2024-08-09', 'Male', 1236548529, 'B.Tech', 'kunj@gmail.com', '$2y$10$3IqUOVQ.vogVnW9P2cZiS.fKLO.08dLbtDyAQTfZVowXrC03osYXm', '1236480.jpg', 'kunj700', 'approved', '', 'In what city were you born?', '$2y$10$YF1c98FI5yUD8BjenxW27OUz3nKB6CRVZ1x1HMEP/90Qet7a1fdNu', 'What is your favorite book?', '$2y$10$/gYWFrHyE8tsFJK0k6WlVeUy5iDrHwjHu/olRpIPP5s17XRcd0zB6'),
(8, 'het patel', '22000150', '2023-10-12', 'Male', 2147483647, 'B.Sc', 'het32@gmail.com', 'het123456', '1236480.jpg', 'het150', 'approved', '', 'In what city were you born?', 'rajkot', 'What was the make of your first car?', 'tata1'),
(9, 'rudra bhatt', '779654123', '2024-09-05', 'Male', 2147483647, 'M.Tech', 'rudra98@gmail.com', 'ru123456', '1236480.jpg', 'rudra123', 'approved', '', 'In what city were you born?', 'gujarat', 'What was the make of your first car?', 'neno'),
(10, 'Aryan Jadhav', '23000333', '2024-08-09', 'Male', 2147483647, 'B.Sc', 'aryan333@gmail.com', 'aryan123456', '1236480.jpg', 'Aryan333', 'approved', '', 'In what city were you born?', 'vadoadra', 'What is your favorite book?', 'english'),
(11, 'John cena', '95126347', '2024-03-06', 'Male', 2147483647, 'M.Tech', 'johncena@gmail.com', 'abcd123', '1236480.jpg', 'John347', 'pending', NULL, 'In what city were you born?', 'uk', 'What was the make of your first car?', 'tata'),
(12, 'Vijay Patel', '23000999', '2024-10-03', 'Male', 2147483647, 'M.Tech', 'vijay@gmail.com', 'aaa123', '1236480.jpg', 'Vijay999', 'approved', '', 'In what city were you born?', 'vadodara', 'What was the make of your first car?', 'tata'),
(13, 'Rudra Bhatt', '23000306', '2001-01-01', 'Male', 2147483647, 'B.Tech', 'rudra@gmail.com', 'rudra123', '0leveldiagram.png', 'Rudra306', 'pending', NULL, 'In what city were you born?', 'vadodara', 'What is your favorite book?', 'Software Engineering'),
(14, 'Vishal rajput', '258963147', '2005-06-07', 'Male', 2147483647, 'B.Tech', 'vishal147@gmail.com', 'vishal123', '1236480.jpg', 'Vishal147', 'rejected', '', 'In what city were you born?', 'vadodara', 'What is your favorite book?', 'html'),
(16, 'Yash', '23000895', '2024-11-01', 'Male', 2147483647, 'B.Tech', 'yash@gmail.com', 'yash123', '1236480.jpg', 'Yash895', 'approved', '', 'In what city were you born?', 'vadodara', 'What is your favorite book?', 'html'),
(17, 'Nirav Patel', '62000888', '2024-11-01', 'Male', 2147483647, 'B.Tech', 'nirav@gmail.com', 'nirav123', '1236480.jpg', 'Nirav888', 'pending', NULL, 'In what city were you born?', 'vadodara', 'What is your favorite book?', 'html');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_number` (`exam_number`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exam_id` (`exam_id`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_enrollment_id` (`enrollment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `registration`
--
ALTER TABLE `registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_exam_id` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
