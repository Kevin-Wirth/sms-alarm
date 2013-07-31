-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 31. Jul 2013 um 16:46
-- Server Version: 5.5.27
-- PHP-Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `smsalarm`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `event-mapping`
--

CREATE TABLE IF NOT EXISTS `event-mapping` (
  `emid` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(5) NOT NULL,
  `uid` int(11) NOT NULL,
  `prio` varchar(2) COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`emid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=67 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `historie-fms`
--

CREATE TABLE IF NOT EXISTS `historie-fms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kennung` int(8) NOT NULL,
  `status` int(2) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `historie-zvei`
--

CREATE TABLE IF NOT EXISTS `historie-zvei` (
  `hid-zvei` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `zvei` int(5) NOT NULL,
  `weckruf` int(1) NOT NULL,
  PRIMARY KEY (`hid-zvei`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `eid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `value` varchar(500) COLLATE latin1_german2_ci NOT NULL,
  `error` tinyint(1) NOT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mapping`
--

CREATE TABLE IF NOT EXISTS `mapping` (
  `value` int(11) NOT NULL,
  `bezeichnung` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rg-positionen`
--

CREATE TABLE IF NOT EXISTS `rg-positionen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rg-stelle` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `text` varchar(150) COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rg-stellen`
--

CREATE TABLE IF NOT EXISTS `rg-stellen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  `typ` varchar(5) COLLATE latin1_german2_ci NOT NULL,
  `nummer` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  `re-id` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
