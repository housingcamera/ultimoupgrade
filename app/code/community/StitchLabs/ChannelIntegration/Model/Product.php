<?php
/**
 * StitchLabs_ChannelIntegration extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       StitchLabs
 * @package        StitchLabs_ChannelIntegration
 * @copyright      Copyright (c) 2014
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Test_Label model
 *
 * @category    StitchLabs
 * @package     StitchLabs_ChannelIntegration
 * @author      Ultimate Module Creator
 */
class StitchLabs_ChannelIntegration_Model_Product
    extends Mage_Core_Model_Abstract {
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'stitchlabs_channelintegration_product';
    const CACHE_TAG = 'stitchlabs_channelintegration_product';
    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'stitchlabs_channelintegration_product';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'product';
    /**
     * constructor
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function _construct(){
        parent::_construct();
        $this->_init('stitchlabs_channelintegration/product');
    }
    /**
     * before save Product
     * @access protected
     * @return StitchLabs_ChannelIntegration_Model_Product
     * @author Ultimate Module Creator
     */
    protected function _beforeSave(){
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()){
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }
    /**
     * save product relation
     * @access public
     * @return StitchLabs_ChannelIntegration_Model_Product
     * @author Ultimate Module Creator
     */
    protected function _afterSave() {
        return parent::_afterSave();
    }
    /**
     * get default values
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function getDefaultValues() {
        $values = array();
        $values['status'] = 1;
        return $values;
    }
}
