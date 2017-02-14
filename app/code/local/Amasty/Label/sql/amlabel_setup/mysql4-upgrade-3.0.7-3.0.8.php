<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `attr_multi` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `attr_code`;
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `attr_rule` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `attr_multi`;
");

$this->endSetup();