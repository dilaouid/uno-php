-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  mer. 29 juil. 2020 à 16:03
-- Version du serveur :  5.7.26
-- Version de PHP :  7.3.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `uno`
--

-- --------------------------------------------------------

--
-- Structure de la table `uno_room`
--

DROP TABLE IF EXISTS `uno_room`;
CREATE TABLE IF NOT EXISTS `uno_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(15) CHARACTER SET latin1 NOT NULL,
  `admin` varchar(255) CHARACTER SET latin1 NOT NULL,
  `nb_players` tinyint(4) NOT NULL DEFAULT '2',
  `players` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `lastcard` varchar(60) DEFAULT NULL,
  `deck` longtext,
  `pile` json DEFAULT NULL,
  `turn` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `turnover` tinyint(1) NOT NULL DEFAULT '1',
  `nb` smallint(6) NOT NULL DEFAULT '0',
  `msg` varchar(255) DEFAULT NULL,
  `uno` tinyint(1) NOT NULL DEFAULT '0',
  `effect` tinyint(1) NOT NULL DEFAULT '0',
  `open` tinyint(1) NOT NULL DEFAULT '1',
  `creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
