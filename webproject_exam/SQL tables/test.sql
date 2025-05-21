-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2024 at 04:47 AM
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
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `exam_number` varchar(50) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `exam_number`, `subject_id`, `is_active`, `created_at`) VALUES
(1, '001', 1, 1, '2024-10-30 09:48:54');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `option1` varchar(255) NOT NULL,
  `option2` varchar(255) NOT NULL,
  `option3` varchar(255) NOT NULL,
  `option4` varchar(255) NOT NULL,
  `correct_option` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `exam_id`, `question_text`, `option1`, `option2`, `option3`, `option4`, `correct_option`) VALUES
(2, 1, '2+2?', '4', '5', '6', '9', 1);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `quiz_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `quiz_name`) VALUES
(1, 'Sample Quiz 1');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `enrollment_id` varchar(20) NOT NULL,
  `exam_number` varchar(50) NOT NULL,
  `score` int(11) NOT NULL,
  `wrong_answers` text DEFAULT NULL,
  `attempt_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`) VALUES
(1, 'MATHS');

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
(13, 'Rudra Bhatt', '23000306', '2001-01-01', 'Male', 2147483647, 'B.Tech', 'rudra@gmail.com', 'rudra123', '0leveldiagram.png', 'Rudra306', 'pending', NULL, 'In what city were you born?', 'vadodara', 'What is your favorite book?', 'Software Engineering');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_number` (`exam_number`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1002;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
