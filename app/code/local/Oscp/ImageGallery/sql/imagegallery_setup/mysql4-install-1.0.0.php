<?php
/**
* @category Oscp
* @package Oscp_ImageGallery
*/ 

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('imagegallery')};
CREATE TABLE {$this->getTable('imagegallery')} (
  `imagegallery_id` int(11) unsigned NOT NULL auto_increment,
  `store_id` INT( 11 ) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `largeimage` varchar(255) NOT NULL default '',
  `smallimage` varchar(255) NOT NULL default '',
  `description` text NULL,
  `status` smallint(6) NOT NULL default '0',
  `weblink` varchar(255) NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`imagegallery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
