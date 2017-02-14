<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `is_single` TINYINT NOT NULL AFTER `label_id`;
    ALTER TABLE `{$this->getTable('amlabel/label')}` ADD `pos`      SMALLINT NOT NULL AFTER `label_id`;
");

$this->endSetup();