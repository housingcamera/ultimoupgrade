<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Follow Up Email
 * @version   1.0.14
 * @build     630
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



/**
 * @var Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addForeignKey(
    $installer->getFkName('email/event_trigger', 'event_id', 'email/event', 'event_id'),
    $installer->getTable('email/event_trigger'),
    'event_id',
    $installer->getTable('email/event'),
    'event_id',
    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE,
    Varien_Db_Adapter_Interface::FK_ACTION_NO_ACTION
);

$installer->getConnection()->resetDdlCache();
$installer->endSetup();
