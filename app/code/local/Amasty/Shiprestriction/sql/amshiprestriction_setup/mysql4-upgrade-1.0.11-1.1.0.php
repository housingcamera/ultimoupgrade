<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */
$this->startSetup();

$this->run("
 ALTER TABLE `{$this->getTable('amshiprestriction/rule')}` ADD `discount_id` int NOT NULL default 0 AFTER `name`;
 ALTER TABLE `{$this->getTable('amshiprestriction/rule')}` ADD `coupon` varchar(255) AFTER `name`;
"); 

$this->endSetup();