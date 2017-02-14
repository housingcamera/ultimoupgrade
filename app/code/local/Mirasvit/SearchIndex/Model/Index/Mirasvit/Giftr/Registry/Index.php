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
 * @package   Sphinx Search Ultimate
 * @version   2.3.4
 * @build     1364
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_SearchIndex_Model_Index_Mirasvit_Giftr_Registry_Index extends Mirasvit_SearchIndex_Model_Index
{
    public function getBaseGroup()
    {
        return 'Mirasvit';
    }

    public function getBaseTitle()
    {
        return 'Gift Registry';
    }

    public function canUse()
    {
        return Mage::getConfig()->getModuleConfig('Mirasvit_Giftr')->is('active', 'true');
    }

    public function getPrimaryKey()
    {
        return 'registry_id';
    }

    public function getAvailableAttributes()
    {
        $result = array(
            'name' => Mage::helper('searchindex')->__('Name'),
            'firstname' => Mage::helper('searchindex')->__('Registrant First Name'),
            'lastname' => Mage::helper('searchindex')->__('Registrant Last Name'),
            'co_firstname' => Mage::helper('searchindex')->__('Co-Registrant First Name'),
            'co_lastname' => Mage::helper('searchindex')->__('Co-Registrant Last Name'),
            'email' => Mage::helper('searchindex')->__('Registrant email'),
            'location' => Mage::helper('searchindex')->__('Event Location'),
            'description' => Mage::helper('searchindex')->__('Description'),
            'uid' => Mage::helper('searchindex')->__('ID'),
        );

        return $result;
    }

    public function getCollection()
    {
        $collection = Mage::getModel('giftr/registry')->getCollection();
        $collection->addFieldToFilter('main_table.is_active', 1);
        $collection->addFieldToFilter('main_table.website_id', Mage::app()->getWebsite()->getId());
        $this->joinMatched($collection, 'main_table.registry_id');

        // Check if a search performed by UID
        $uidCollection = Mage::getModel('giftr/registry')->getCollection()
            ->addFieldToFilter('uid', $this->getQuery()->getQueryText());
        if ($uidCollection->getSize()) {
            $collection->addFieldToFilter('uid', $this->getQuery()->getQueryText());
        } else {
            // Otherwise search only within pulic registries
            $collection->addFieldToFilter('is_public', 1);
        }

        return $collection;
    }
}
