<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */
$this->startSetup();

$this->run("
ALTER TABLE `{$this->getTable('amshiprestriction/rule')}`  ADD `for_admin` TINYINT NOT NULL AFTER `is_active`;
"); 

$this->endSetup();