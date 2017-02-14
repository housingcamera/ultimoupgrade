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

class Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Recur
{

    var $params;
    var $recurTemplate = array(
        'recur_unit',
        'start_now',
        'start_date',
        'num_recurs',
        'period',
        'recur_amount'
    );

    function mpgRecur($params)
    {
        $this->params = $params;

        if ((!$this->params['period'])) {
            $this->params['period'] = 1;
        }
    }

    function toXML()
    {
        foreach ($this->recurTemplate as $tag) {
            $xmlString .= "<$tag>" . $this->params[$tag] . "</$tag>";
        }

        return "<recur>$xmlString</recur>";
    }

}
