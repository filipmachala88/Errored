-- Adminer 4.8.1 MySQL 5.7.24 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `blog` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `blog`;

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;

CREATE TRIGGER `copy_posts_create` BEFORE INSERT ON `comments` FOR EACH ROW
SET NEW.created_at = NOW(),
 NEW.updated_at = NOW();;

CREATE TRIGGER `copy_posts_update` BEFORE UPDATE ON `comments` FOR EACH ROW
SET NEW.updated_at = NOW(),
 NEW.created_at = OLD.created_at;;

DELIMITER ;

DROP TABLE IF EXISTS `phpauth_attempts`;
CREATE TABLE `phpauth_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` char(39) NOT NULL,
  `expiredate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `phpauth_attempts` (`id`, `ip`, `expiredate`) VALUES
(1,	'::1',	'2022-03-03 10:31:05');

DROP TABLE IF EXISTS `phpauth_config`;
CREATE TABLE `phpauth_config` (
  `setting` varchar(100) NOT NULL,
  `value` varchar(100) DEFAULT NULL,
  UNIQUE KEY `setting` (`setting`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `phpauth_config` (`setting`, `value`) VALUES
('allow_concurrent_sessions',	'0'),
('attack_mitigation_time',	'+30 minutes'),
('attempts_before_ban',	'30'),
('attempts_before_verify',	'5'),
('bcrypt_cost',	'10'),
('cookie_domain',	NULL),
('cookie_forget',	'+30 minutes'),
('cookie_http',	'1'),
('cookie_name',	'phpauth_session_cookie'),
('cookie_path',	'/'),
('cookie_remember',	'+1 month'),
('cookie_renew',	'+5 minutes'),
('cookie_samesite',	'Strict'),
('cookie_secure',	'1'),
('custom_datetime_format',	'Y-m-d H:i'),
('emailmessage_suppress_activation',	'0'),
('emailmessage_suppress_reset',	'0'),
('mail_charset',	'UTF-8'),
('password_min_score',	'3'),
('recaptcha_enabled',	'0'),
('recaptcha_secret_key',	''),
('recaptcha_site_key',	''),
('request_key_expiration',	'+10 minutes'),
('site_activation_page',	'activate'),
('site_activation_page_append_code',	'0'),
('site_email',	'no-reply@phpauth.cuonic.com'),
('site_key',	'fghuior.)/!/jdUkd8s2!7HVHG7777ghg'),
('site_language',	'en_GB'),
('site_name',	'PHPAuth'),
('site_password_reset_page',	'reset'),
('site_password_reset_page_append_code',	'0'),
('site_timezone',	'Europe/Paris'),
('site_url',	'https://github.com/PHPAuth/PHPAuth'),
('smtp',	'0'),
('smtp_auth',	'1'),
('smtp_debug',	'0'),
('smtp_host',	'smtp.example.com'),
('smtp_password',	'password'),
('smtp_port',	'25'),
('smtp_security',	NULL),
('smtp_username',	'email@example.com'),
('table_attempts',	'phpauth_attempts'),
('table_emails_banned',	'phpauth_emails_banned'),
('table_requests',	'phpauth_requests'),
('table_sessions',	'phpauth_sessions'),
('table_translations',	'phpauth_translation_dictionary'),
('table_users',	'phpauth_users'),
('translation_source',	'php'),
('verify_email_max_length',	'100'),
('verify_email_min_length',	'5'),
('verify_email_use_banlist',	'1'),
('verify_password_min_length',	'3');

DROP TABLE IF EXISTS `phpauth_emails_banned`;
CREATE TABLE `phpauth_emails_banned` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `phpauth_requests`;
CREATE TABLE `phpauth_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `token` char(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `expire` datetime NOT NULL,
  `type` enum('activation','reset') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `token` (`token`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `phpauth_sessions`;
CREATE TABLE `phpauth_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `hash` char(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `expiredate` datetime NOT NULL,
  `ip` varchar(39) NOT NULL,
  `device_id` varchar(36) DEFAULT NULL,
  `agent` varchar(200) NOT NULL,
  `cookie_crc` char(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `phpauth_sessions` (`id`, `uid`, `hash`, `expiredate`, `ip`, `device_id`, `agent`, `cookie_crc`) VALUES
(36,	1,	'b365797ac00aaa115ad475b8228fd24374dc83ec',	'2022-04-03 10:01:18',	'::1',	NULL,	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36',	'865d5237ace7b99ee6c7e7a5a1c31a5367eedf90');

DROP TABLE IF EXISTS `phpauth_users`;
CREATE TABLE `phpauth_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '0',
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `phpauth_users` (`id`, `email`, `password`, `isactive`, `dt`) VALUES
(1,	'root@gmail.com',	'$2y$10$RhHv8sEF.U9sfo4FyE9NFuxYt7nhpOAspydbqSs.UlQHvxyf7XQ6K',	1,	'2022-03-01 20:50:55'),
(2,	'gagasd@gmail.com',	'$2y$10$RhHv8sEF.U9sfo4FyE9NFuxYt7nhpOAspydbqSs.UlQHvxyf7XQ6K	',	1,	'2022-03-02 17:06:48');

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL,
  `text` text NOT NULL,
  `slug` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `posts` (`id`, `user_id`, `title`, `text`, `slug`, `created_at`, `updated_at`) VALUES
(1,	1,	'Křtinské super hodycdds',	'Nová věc připsána ddsds\r\nf\r\nLorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis risus. Mauris elementum mauris vitae tortor. Mauris metus. Class aptent taciti sociosqu ad litora torquent per conubia \r\n\r\nnostra, per inceptos hymenaeos. Phasellus rhoncus. Duis risus. Vivamus porttitor turpis ac leo. Nulla non lectus sed nisl molestie malesuada. Suspendisse nisl. Quisque porta. Vivamus ac leo pretium faucibus. Suspendisse nisl. Praesent vitae \r\n\r\nhttps://www.youtube.com/watch?v=CdAvYn4gLZo&ab_channel=PlanetMajk\r\n\r\narcu tempor neque lacinia pretium. Pellentesque arcu. Phasellus enim erat, vestibulum vel, aliquam a, posuere eu, velit. Nunc dapibus tortor vel mi dapibus sollicitudin. Integer rutrum, orci vestibulum ullamcorper ultricies, lacus quam ultricies odio, vitae placerat pede sem sit amet enim.',	'krtinske-hody',	'2022-02-15 10:41:31',	'2022-03-02 15:31:57'),
(2,	2,	'Melodka párty',	'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis risus. Mauris elementum mauris vitae tortor. Mauris metus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Phasellus rhoncus. Duis risus. Vivamus porttitor turpis ac leo. Nulla non lectus sed nisl molestie malesuada. Suspendisse nisl. Quisque porta. Vivamus ac leo pretium faucibus. Suspendisse nisl. Praesent vitae arcu tempor neque lacinia pretium. Pellentesque arcu. Phasellus enim erat, vestibulum vel, aliquam a, posuere eu, velit. Nunc dapibus tortor vel mi dapibus sollicitudin. Integer rutrum, orci vestibulum ullamcorper ultricies, lacus quam ultricies odio, vitae placerat pede sem sit amet enim.',	'melodka-party',	'2022-02-15 10:42:10',	'2022-03-02 17:07:20'),
(3,	1,	'Harry Potter: Tajemná komnata 2',	'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis risus. Mauris elementum mauris vitae tortor. Mauris metus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Phasellus rhoncus. Duis risus. Vivamus porttitor turpis ac leo. Nulla non lectus sed nisl molestie malesuada. Suspendisse nisl. Quisque porta. Vivamus ac leo pretium faucibus. Suspendisse nisl. Praesent vitae arcu tempor neque lacinia pretium. Pellentesque arcu. Phasellus enim erat, vestibulum vel, aliquam a, posuere eu, velit. Nunc dapibus tortor vel mi dapibus sollicitudin. Integer rutrum, orci vestibulum ullamcorper ultricies, lacus quam ultricies odio, vitae placerat pede sem sit amet enim.',	'harry-potter-tajmena-komnata',	'2022-02-15 10:43:02',	'2022-03-02 15:32:06');

DELIMITER ;;

CREATE TRIGGER `posts_create` BEFORE INSERT ON `posts` FOR EACH ROW
SET NEW.created_at = NOW(),
 NEW.updated_at = NOW();;

CREATE TRIGGER `posts_update` BEFORE UPDATE ON `posts` FOR EACH ROW
SET NEW.updated_at = NOW(),
 NEW.created_at = OLD.created_at;;

DELIMITER ;

DROP TABLE IF EXISTS `posts_tags`;
CREATE TABLE `posts_tags` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `posts_tags` (`post_id`, `tag_id`) VALUES
(2,	1),
(2,	4),
(3,	1),
(3,	5),
(3,	4),
(3,	3),
(3,	2),
(1,	5),
(1,	4),
(1,	3);

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tags` (`id`, `tag`) VALUES
(1,	'akce'),
(2,	'zprávy'),
(3,	'oznámení'),
(4,	'hudba'),
(5,	'filmy');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '0',
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2022-03-03 09:15:48