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

function replaceCondition(&$item, &$key) {
    if ($item == 'catalogrule/rule_condition_product') {
        $item = 'awcountdown/countdown_condition_combine_product';
    }
}
$installer = $this;
$installer->startSetup();
try {
    $installer->run("
        ALTER TABLE `{$this->getTable('awcountdown/countdown')}` ADD `product_action_disable` TINYINT NOT NULL DEFAULT '0' AFTER `customer_group_ids`;
        ALTER TABLE `{$this->getTable('awcountdown/countdown')}` ADD `product_action_enable` TINYINT NOT NULL DEFAULT '0' AFTER `product_action_disable`;
        ALTER TABLE `{$this->getTable('awcountdown/countdown')}` ADD `recurring_enabled` TINYINT NOT NULL DEFAULT '0' AFTER `product_action_enable`;
        ALTER TABLE `{$this->getTable('awcountdown/countdown')}` ADD `recurring_data` TEXT NOT NULL AFTER `recurring_enabled`;
        ALTER TABLE `{$this->getTable('awcountdown/countdown')}` ADD `priority` INT(10) unsigned AFTER `recurring_data`;
        CREATE TABLE IF NOT EXISTS `{$this->getTable('awcountdown/countdown_index')}` (
          `countdown_id` int(10) unsigned NOT NULL,
          `product_id` int(10) unsigned NOT NULL,
          `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`countdown_id`,`product_id`, `store_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        CREATE TABLE IF NOT EXISTS `{$this->getTable('awcountdown/countdown_index_idx')}` (
          `countdown_id` int(10) unsigned NOT NULL,
          `product_id` int(10) unsigned NOT NULL,
          `store_id` smallint(5) unsigned NOT NULL,
        PRIMARY KEY (`countdown_id`,`product_id`, `store_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $countDownCollection = Mage::getModel('awcountdown/countdown')->getCollection();
    foreach ($countDownCollection as $countDown) {
        $conditions = unserialize($countDown->getData('conditions_serialized'));
        array_walk_recursive($conditions, 'replaceCondition');
        $this->run("
            UPDATE {$this->getTable('awcountdown/countdown')} SET `conditions_serialized` = '" . serialize($conditions)
                . "' WHERE `countdownid` = {$countDown->getId()};
        ");
    }
} catch (Exception $ex) {
    Mage::logException($ex);
}
$installer->endSetup();