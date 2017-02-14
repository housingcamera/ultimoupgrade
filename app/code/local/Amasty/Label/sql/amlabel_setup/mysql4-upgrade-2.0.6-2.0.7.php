<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amlabel/label')}` MODIFY COLUMN `category` varchar(255) NOT NULL;
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `special_price_only` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `is_sale`;
");

$this->endSetup();