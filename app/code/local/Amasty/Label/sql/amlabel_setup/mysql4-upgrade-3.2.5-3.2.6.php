<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `is_active` TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER `name`;
");

$this->endSetup();
