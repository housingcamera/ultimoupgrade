<?php
/**
 * Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 *
 * @extension   eSELECTplus eSELECTplus Canada payment suite
 * @type        Payment method
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Magento
 * @package     Appmerce_EselectPlus
 * @copyright   Copyright (c) 2011-2014 Appmerce (http://www.appmerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Appmerce_EselectPlus_Model_Mysql4_Setup */

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('eselectplus/api_debug')}`;
CREATE TABLE `{$this->getTable('eselectplus/api_debug')}` (
  `debug_id` int(10) unsigned NOT null auto_increment,
  `debug_at` timestamp NOT null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `dir` enum('in', 'out'),
  `url` varchar(255),
  `data` text,
  PRIMARY KEY  (`debug_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();

$installer->addAttribute('order_payment', 'appmerce_response_code', array('type' => 'int'));
