<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amlabel/label')}` 
    ADD COLUMN `from_date` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `stock_status`,
 	ADD COLUMN `to_date` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `from_date`,
 	ADD COLUMN `date_range_enabled` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `to_date`;
");

$this->endSetup();