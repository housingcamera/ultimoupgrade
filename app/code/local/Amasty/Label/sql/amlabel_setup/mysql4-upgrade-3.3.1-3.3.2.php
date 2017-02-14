<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `prod_label_color` varchar(255) NOT NULL AFTER `prod_img`;
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `cat_label_color`  varchar(255) NOT NULL AFTER `cat_img`;
");

$this->endSetup();
