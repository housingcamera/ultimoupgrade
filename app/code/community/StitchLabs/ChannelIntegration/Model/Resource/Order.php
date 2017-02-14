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
 * Test_Label resource model
 *
 * @category    StitchLabs
 * @package     StitchLabs_ChannelIntegration
 * @author      Ultimate Module Creator
 */
class StitchLabs_ChannelIntegration_Model_Resource_Order
    extends Mage_Core_Model_Resource_Db_Abstract {
    /**
     * constructor
     * @access public
     * @author Ultimate Module Creator
     */
    public function _construct(){
        $this->_init('stitchlabs_channelintegration/order', 'entity_id');
    }
}
