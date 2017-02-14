<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD COLUMN `cat_image_width` varchar(255) NOT NULL;
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD COLUMN `cat_image_height` varchar(255) NOT NULL;
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD COLUMN `prod_image_width` varchar(255) NOT NULL;
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD COLUMN `prod_image_height` varchar(255) NOT NULL;
");

$this->endSetup();