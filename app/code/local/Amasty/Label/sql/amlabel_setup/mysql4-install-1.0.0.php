<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("

CREATE TABLE `{$this->getTable('amlabel/label')}` (
  `label_id` mediumint(9) NOT NULL auto_increment,
  `name`   varchar(255) NOT NULL,
  `stores` varchar(255) NOT NULL,
  
  `prod_txt`   varchar(255) NOT NULL,
  `prod_img`   varchar(255) NOT NULL,
  `prod_pos`   tinyint(1) NOT NULL,
  `prod_style` varchar(255) NOT NULL,
  
  `cat_txt`   varchar(255) NOT NULL,
  `cat_img`   varchar(255) NOT NULL,
  `cat_pos`   tinyint(1) NOT NULL,
  `cat_style` varchar(255) NOT NULL,
  
  `is_new`   tinyint(1) NOT NULL,
  `is_sale`  tinyint(1) NOT NULL,
  
  `include_type` tinyint(1) NOT NULL,
  `include_sku`  varchar(255) NOT NULL,
  
  `category`  mediumint(9) NOT NULL,
  `attr_code` varchar(255) NOT NULL,
  `attr_value` varchar(255) NOT NULL,
  `stock_less` int(11) NOT NULL,
  `stock_more` int(11) NOT NULL,
  `stock_status` tinyint(4) NOT NULL,
  PRIMARY KEY  (`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
");

$this->endSetup(); 