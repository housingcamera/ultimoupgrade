<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */

$installer = $this;
$installer->startSetup();


$installer->run("
CREATE TABLE {$installer->getTable('amregions/region')} (
  `region_id` int(10) unsigned NOT NULL auto_increment,
  `region_title` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->run("
INSERT INTO `{$installer->getTable('amregions/region')}` (`region_id`, `region_title`) VALUES (1, 'Europe');
");

$installer->run("
CREATE TABLE {$installer->getTable('amregions/region_country')} (
  `region_country_id` int(10) unsigned NOT NULL auto_increment,
  `region_id` int(10) unsigned NOT NULL,
  `country_id` VARCHAR(2) NOT NULL DEFAULT '' COMMENT 'Country Id in ISO-2',
  PRIMARY KEY  (`region_country_id`),
  CONSTRAINT `FK_AMREGIONS_REGION_COUNTRY_REGION` FOREIGN KEY (`region_id`) REFERENCES {$installer->getTable('amregions/region')} (`region_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_AMREGIONS_REGION_COUNTRY_COUNTRY` FOREIGN KEY (`country_id`) REFERENCES {$installer->getTable('directory/country')} (`country_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->run("
INSERT INTO `{$installer->getTable('amregions/region_country')}` (`region_country_id`, `region_id`, `country_id`) VALUES
(1, 1, 'AD'),
(2, 1, 'AT'),
(3, 1, 'BY'),
(4, 1, 'BE'),
(5, 1, 'BA'),
(6, 1, 'BG'),
(7, 1, 'HR'),
(8, 1, 'CZ'),
(9, 1, 'DK'),
(10, 1, 'EE'),
(11, 1, 'FI'),
(12, 1, 'FR'),
(13, 1, 'DE'),
(14, 1, 'GR'),
(15, 1, 'HU'),
(16, 1, 'IE'),
(17, 1, 'IT'),
(18, 1, 'LV'),
(19, 1, 'LI'),
(20, 1, 'LT'),
(21, 1, 'LU'),
(22, 1, 'MC'),
(23, 1, 'NL'),
(24, 1, 'NO'),
(25, 1, 'PL'),
(26, 1, 'PT'),
(27, 1, 'RO'),
(28, 1, 'RS'),
(29, 1, 'ES'),
(30, 1, 'SE'),
(31, 1, 'CH'),
(32, 1, 'UA'),
(33, 1, 'GB');
");

$installer->endSetup();
