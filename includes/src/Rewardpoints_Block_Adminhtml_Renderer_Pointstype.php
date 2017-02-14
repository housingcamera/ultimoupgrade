<?php
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Rewardpoints_Block_Adminhtml_Renderer_Pointstype extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());
        
        $order_id = $row->getData($this->getColumn()->getIndex());
        
        $model = Mage::getModel('rewardpoints/stats');
        
        $points_type = $model->getPointsDefaultTypeToArray();
        $points_type[Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN] = Mage::helper('rewardpoints')->__('Store input %s', ($row->getRewardpointsDescription()) ? ' - '.$row->getRewardpointsDescription() : '');
        
        if ( ($order_id > 0) || ($order_id != "" && !is_numeric($order_id)) ){
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            return Mage::helper('rewardpoints')->__('Points related to order #%s (%s)', $order_id, Mage::helper('rewardpoints')->__($order->getData($status_field)));
        } elseif (isset($points_type[$order_id])) {
            return $points_type[$order_id];
        } else {
            return null;
        }
    }
}

