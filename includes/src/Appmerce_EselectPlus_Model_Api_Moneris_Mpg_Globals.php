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

class Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Globals
{
    function __construct()
    {
        // default
    }

    /**
     * Return payment API model
     *
     * @return Appmerce_EselectPlus_Model_Api_Hosted
     */
    protected function getApi()
    {
        return Mage::getSingleton('eselectplus/api_direct');
    }

    function getGlobals()
    {
        $Globals = array(
            'MONERIS_PROTOCOL' => 'https',
            'MONERIS_HOST' => $this->getApi()->getConfigData('test_flag') ? 'esqa.moneris.com' : 'www3.moneris.com',
            'MONERIS_PORT' => '443',
            'MONERIS_FILE' => '/gateway2/servlet/MpgRequest',
            'API_VERSION' => 'PHP - 2.5.1',
            'CLIENT_TIMEOUT' => '60'
        );
        return ($Globals);
    }

}
