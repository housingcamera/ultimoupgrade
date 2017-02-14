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
class StitchLabs_ChannelIntegration_Model_Product_Api_V2
    extends StitchLabs_ChannelIntegration_Model_Product_Api {
    /**
     * Test_Label info
     * @access public
     * @param int $productId
     * @return object
     * @author Ultimate Module Creator
     */
    public function info($productId, $store = null, $attributes = null, $identifierType = null){
        $result = parent::info($productId);
        $result = Mage::helper('api')->wsiArrayPacker($result);
        return $result;
    }
}
