CREATE TABLE `prefix_block_course_menu` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `name` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `version` bigint(10) unsigned NOT NULL default '0',
  `cron` bigint(10) unsigned NOT NULL default '0',
  `lastcron` bigint(10) unsigned NOT NULL default '0',
  `visible` tinyint(1) NOT NULL default '1',
  `multiple` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='to store installed blocks' ;