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


class AW_Countdown_Model_Indexer extends Mage_Index_Model_Indexer_Abstract
{
    protected $_matchedEntities = array(
        AW_Countdown_Model_Countdown::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
        ),
        Mage_Catalog_Model_Convert_Adapter_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );

    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $entity = $event->getEntity();
        if ($entity == Mage_Catalog_Model_Product::ENTITY && $event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
            $this->_registerCatalogProductDeleteEvent($event);
        }
        if ($entity == AW_Countdown_Model_Countdown::ENTITY && $event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
            $this->_registerCountdownDeleteEvent($event);
        }
    }

    public function getName()
    {
        return Mage::helper('awcountdown')->__('Countdown');
    }

    public function getDescription()
    {
        return Mage::helper('awcountdown')->__('Index product attributes for countdown timer building');
    }

    protected function _getResource()
    {
        return Mage::getResourceModel('awcountdown/indexer');
    }

    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        if (in_array($event->getEntity(), array(AW_Countdown_Model_Countdown::ENTITY, Mage_Catalog_Model_Product::ENTITY))) {
            $this->callEventHandler($event);
        } else {
            $process = $event->getProcess();
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
    }

    protected function _registerCatalogProductDeleteEvent(Mage_Index_Model_Event $event)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $event->getDataObject();
        if ($product->getId()) {
            $event->addNewData('delete_product_id', $product->getId());
        }
        return $this;
    }

    protected function _registerCountdownDeleteEvent(Mage_Index_Model_Event $event)
    {
        $countdownModel = $event->getDataObject();
        if ($countdownModel->getId()) {
            $event->addNewData('delete_countdown_id', $countdownModel->getId());
        }
        return $this;
    }
}