<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `customer_groups` varchar(255) NOT NULL AFTER `price_range_enabled`;
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `customer_group_enabled` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `customer_groups`;
");

$this->endSetup();