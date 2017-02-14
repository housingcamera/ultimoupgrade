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

class Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Transaction
{

    var $txn;
    var $custInfo = null;
    var $avsInfo = null;
    var $cvdInfo = null;
    var $recur = null;

    function __construct($txn)
    {

        $this->txn = $txn;

    }

    function getCustInfo()
    {
        return $this->custInfo;
    }

    function setCustInfo($custInfo)
    {
        $this->custInfo = $custInfo;
        array_push($this->txn, $custInfo);
    }

    function getCvdInfo()
    {
        return $this->cvdInfo;
    }

    function setCvdInfo($cvdInfo)
    {
        $this->cvdInfo = $cvdInfo;
    }

    function getAvsInfo()
    {
        return $this->avsInfo;
    }

    function setAvsInfo($avsInfo)
    {
        $this->avsInfo = $avsInfo;
    }

    function getRecur()
    {
        return $this->recur;
    }

    function setRecur($recur)
    {
        $this->recur = $recur;
    }

    function getTransaction()
    {

        return $this->txn;
    }

}
