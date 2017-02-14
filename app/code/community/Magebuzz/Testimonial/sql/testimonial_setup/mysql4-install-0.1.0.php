<?php
/*
* Copyright (c) 2014 www.magebuzz.com
*/
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('simple_testimonial')};
CREATE TABLE {$this->getTable('simple_testimonial')} (
  `testimonial_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `website` varchar(255) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `address` varchar(255) NOT NULL default '',
  `testimonial` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`testimonial_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
	
$installer->endSetup(); 
