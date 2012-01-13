-- phpMyAdmin SQL Dump
-- version 3.3.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 13, 2012 at 10:55 AM
-- Server version: 5.0.92
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `crambu_toppedin`
--

-- --------------------------------------------------------

--
-- Table structure for table `hidden_trends`
--

CREATE TABLE IF NOT EXISTS `hidden_trends` (
  `hide_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `text` varchar(25) NOT NULL,
  PRIMARY KEY  (`hide_id`),
  UNIQUE KEY `hide_id` (`hide_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='trends that users have requested to hide from their feeds' AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `logins`
--

CREATE TABLE IF NOT EXISTS `logins` (
  `login_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `datetime_login` datetime NOT NULL,
  PRIMARY KEY  (`login_id`),
  UNIQUE KEY `login_id` (`login_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=252 ;

-- --------------------------------------------------------

--
-- Table structure for table `snapshots`
--

CREATE TABLE IF NOT EXISTS `snapshots` (
  `snapshot_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `method` int(1) NOT NULL COMMENT '0 = manual, 1 = cron',
  `time_to_create` float NOT NULL,
  `share_key` varchar(25) NOT NULL COMMENT 'token to share to view trends',
  `times_shared` int(11) NOT NULL,
  UNIQUE KEY `snapshot_id` (`snapshot_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='stored snapshots of trends' AUTO_INCREMENT=554 ;

-- --------------------------------------------------------

--
-- Table structure for table `storage_trending_tweets`
--

CREATE TABLE IF NOT EXISTS `storage_trending_tweets` (
  `storage_entry_id` int(11) NOT NULL auto_increment,
  `entry_id` int(11) NOT NULL,
  `trend_id` int(11) NOT NULL,
  `twitter_tweet_id` bigint(20) NOT NULL,
  `twitter_name` varchar(50) NOT NULL,
  `twitter_screen_name` varchar(20) NOT NULL,
  `twitter_image` varchar(500) NOT NULL,
  `tweet` varchar(160) NOT NULL COMMENT 'text of tweet',
  `tweet_datetime` bigint(20) NOT NULL,
  PRIMARY KEY  (`storage_entry_id`),
  UNIQUE KEY `storage_entry_id` (`storage_entry_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=85373 ;

-- --------------------------------------------------------

--
-- Table structure for table `trending_tweets`
--

CREATE TABLE IF NOT EXISTS `trending_tweets` (
  `entry_id` int(11) NOT NULL auto_increment,
  `trend_id` int(11) NOT NULL,
  `twitter_tweet_id` bigint(20) NOT NULL,
  `twitter_name` varchar(50) NOT NULL,
  `twitter_screen_name` varchar(20) NOT NULL,
  `twitter_image` varchar(500) NOT NULL,
  `tweet` varchar(160) NOT NULL COMMENT 'text of tweet',
  `tweet_datetime` bigint(20) NOT NULL,
  PRIMARY KEY  (`entry_id`),
  UNIQUE KEY `entry_id` (`entry_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=85373 ;

-- --------------------------------------------------------

--
-- Table structure for table `trends`
--

CREATE TABLE IF NOT EXISTS `trends` (
  `trend_id` int(11) NOT NULL auto_increment,
  `snapshot_id` int(11) NOT NULL,
  `score` float NOT NULL,
  `count` int(11) NOT NULL,
  `text` varchar(140) NOT NULL,
  `type` int(1) NOT NULL COMMENT '0 = #, 1 = @, 2 = word',
  PRIMARY KEY  (`trend_id`),
  UNIQUE KEY `trend_id` (`trend_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='trends = hashtags, mentions and words' AUTO_INCREMENT=6599 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `twitter_name` varchar(20) NOT NULL,
  `twitter_id` int(11) NOT NULL,
  `date_registered` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  `take_snapshots` int(1) NOT NULL COMMENT '0 = no, 1 = yes',
  `location` varchar(50) NOT NULL,
  `friends_count` int(11) NOT NULL,
  `status_count` int(11) NOT NULL,
  `followers_count` int(11) NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=212 ;
