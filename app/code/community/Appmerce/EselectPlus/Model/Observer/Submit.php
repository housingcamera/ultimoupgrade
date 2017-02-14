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

class Appmerce_EselectPlus_Model_Observer_Submit
{
    /*
     * Keep cart after placing order
     */
    public function sales_model_service_quote_submit_after(Varien_Event_Observer $observer)
    {
        $method = $observer->getEvent()->getOrder()->getPayment()->getMethod();

        // Only non-gateway methods
        if (strpos($method, 'eselectplus_') !== false && !in_array($method, array('eselectplus_direct'))) {
            $observer->getQuote()->setIsActive(true);
        }
    }

}
