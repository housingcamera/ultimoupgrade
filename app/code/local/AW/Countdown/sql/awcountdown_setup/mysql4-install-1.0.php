<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Countdown
 * @version    1.1.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        CREATE TABLE IF NOT EXISTS `{$this->getTable('awcountdown/countdown')}` (
            `countdownid` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` TINYTEXT NOT NULL,
            `is_enabled` TINYINT NOT NULL DEFAULT '0',
            `date_from` DATETIME NULL,
            `date_to` DATETIME NULL,
            `store_ids` TINYTEXT NOT NULL,
            `url` MEDIUMTEXT NOT NULL,
            `block_title` TINYTEXT NOT NULL,
            `design` TINYTEXT NOT NULL,
            `show_format` TINYTEXT NOT NULL,
            `template` MEDIUMTEXT NOT NULL,
            `status` TINYINT NOT NULL DEFAULT '0',
            `autom_display` tinyint(4) NULL,
            `conditions_serialized` MEDIUMTEXT NULL

        ) ENGINE = MyISAM DEFAULT CHARSET=utf8;

         CREATE TABLE IF NOT EXISTS `{$this->getTable('awtrigger/trigger')}` (
            `trigger_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `timer_id` TINYINT NOT NULL,
            `rule_id` TINYINT NOT NULL,
            `rule_type` TINYTEXT NOT NULL,
            `action_type` TINYINT NOT NULL DEFAULT '0',
            `action` TINYINT NOT NULL DEFAULT '0'            
        ) ENGINE = MyISAM DEFAULT CHARSET=utf8;

    ");
} catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
