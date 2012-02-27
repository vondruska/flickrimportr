
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


-- --------------------------------------------------------

--
-- Table structure for table `flickr_cache`
--

CREATE TABLE IF NOT EXISTS `flickr_cache` (
  `request` char(35) NOT NULL,
  `response` mediumtext NOT NULL,
  `expiration` datetime NOT NULL,
  KEY `request` (`request`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `imports`
--

CREATE TABLE IF NOT EXISTS `imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(64) unsigned NOT NULL,
  `flickr_url` varchar(255) NOT NULL,
  `album_id` bigint(64) unsigned NOT NULL,
  `photo_id` bigint(64) unsigned NOT NULL,
  `facebook_url` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `retry` tinyint(1) NOT NULL COMMENT 'If import of picture failed on first try',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=55633 ;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` char(36) NOT NULL,
  `user_id` bigint(64) unsigned NOT NULL,
  `import_object_id` varchar(100) NOT NULL,
  `access_token` varchar(100) NOT NULL,
  `pid` int(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `album_id` bigint(64) unsigned NOT NULL,
  `album_link` varchar(255) NOT NULL,
  `album_privacy` varchar(50) NOT NULL,
  `notify` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `completed` datetime NOT NULL,
  `error` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `reported` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` char(36) NOT NULL,
  `flickr_id` varchar(50) NOT NULL,
  `flickr_url` varchar(255) NOT NULL,
  `aid` bigint(64) unsigned NOT NULL DEFAULT '0',
  `pid` bigint(64) unsigned NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `completed` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=379322 ;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(64) unsigned NOT NULL COMMENT 'Facebook User ID',
  `flickr_user` varchar(35) NOT NULL COMMENT 'Flickr User ID',
  `flickr_auth` varchar(255) NOT NULL COMMENT 'flickr Auth Token',
  `session_id` varchar(2555) NOT NULL,
  `fb_access_token` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `last_seen` datetime NOT NULL,
  `notify` tinyint(1) NOT NULL DEFAULT '0',
  `disabled` tinyint(1) NOT NULL,
  `display_photos` tinyint(1) NOT NULL DEFAULT '0',
  `flickr_name` varchar(255) NOT NULL,
  `use_originals` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
