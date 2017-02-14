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

class Appmerce_EselectPlus_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get real IP address
     *
     * @return string $ip
     */
    public function getRealIpAddr()
    {
        $ip = array_filter(array(
            $_SERVER['HTTP_CLIENT_IP'],
            $_SERVER['HTTP_X_FORWARDED_FOR'],
            $_SERVER['REMOTE_ADDR'],
        ));
        return current($ip);
    }

}
