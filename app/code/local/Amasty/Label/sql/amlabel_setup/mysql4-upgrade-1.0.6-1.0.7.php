<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("    
    ALTER TABLE `{$this->getTable('amlabel/label')}`
    ADD COLUMN `cat_text_style` VARCHAR(2048) NOT NULL DEFAULT '' AFTER `date_range_enabled`,
    ADD COLUMN `prod_text_style` VARCHAR(2048) NOT NULL DEFAULT '' AFTER `cat_text_style`;
");

$this->endSetup();