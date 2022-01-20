-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 28, 2018 at 09:58 AM
-- Server version: 5.5.46
-- PHP Version: 5.6.36

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `notify_onduty`
--

-- --------------------------------------------------------

--
-- Table structure for table `notify_log`
--

CREATE TABLE IF NOT EXISTS `notify_log` (
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `team` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'command call',
  `contact_id` int(4) NOT NULL COMMENT 'contact_id',
  `contact_name` VARCHAR(100) NOT NULL,
  `phone_number` VARCHAR(100) NOT NULL,
  `message` varchar(400) COLLATE utf8_unicode_ci NOT NULL COMMENT 'message text',
  `comment` VARCHAR(100) NULL,
  PRIMARY KEY (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
