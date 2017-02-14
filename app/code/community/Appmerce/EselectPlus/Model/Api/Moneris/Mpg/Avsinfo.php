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

class Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Avsinfo
{

    var $params;
    var $avsTemplate = array(
        'avs_street_number',
        'avs_street_name',
        'avs_zipcode',
        'avs_email',
        'avs_hostname',
        'avs_browser',
        'avs_shiptocountry',
        'avs_shipmethod',
        'avs_merchprodsku',
        'avs_custip',
        'avs_custphone'
    );

    function __construct($params)
    {
        $this->params = $params;
    }

    function toXML()
    {
        foreach ($this->avsTemplate as $tag) {
            $xmlString .= "<$tag>" . $this->params[$tag] . "</$tag>";
        }

        return "<avs_info>$xmlString</avs_info>";
    }

}
