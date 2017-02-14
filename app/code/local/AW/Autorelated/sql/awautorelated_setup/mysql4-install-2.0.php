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
 * @package    AW_Autorelated
 * @version    2.4.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        CREATE TABLE IF NOT EXISTS `{$this->getTable('awautorelated/blocks')}` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `type` TINYINT NOT NULL,
            `name` TINYTEXT NOT NULL,
            `status` TINYINT NOT NULL DEFAULT '1',
            `store` TEXT NOT NULL,
            `customer_groups` TEXT NOT NULL,
            `priority` INT NOT NULL DEFAULT '1',
            `date_from` DATE NULL,
            `date_to` DATE NULL,
            `position` INT NOT NULL,
            `currently_viewed` MEDIUMTEXT NOT NULL,
            `related_products` MEDIUMTEXT NOT NULL
        ) ENGINE = MyISAM DEFAULT CHARSET=utf8;
    ");
} catch (Exception $ex) {
    Mage::logException($ex);
}
$installer->endSetup();