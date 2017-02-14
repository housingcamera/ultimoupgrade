<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("    
    ALTER TABLE `{$this->getTable('amlabel/label')}`
    ADD COLUMN `from_price` DECIMAL(12,4) NOT NULL DEFAULT 0 AFTER `date_range_enabled`,
    ADD COLUMN `to_price` DECIMAL(12,4) NOT NULL DEFAULT 0 AFTER `from_price`,
    ADD COLUMN `price_range_enabled` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `to_price`;
");

$this->endSetup();