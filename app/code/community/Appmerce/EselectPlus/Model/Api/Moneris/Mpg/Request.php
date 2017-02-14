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

class Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Request
{

    var $txnTypes = array(
        'purchase' => array(
            'order_id',
            'cust_id',
            'amount',
            'pan',
            'expdate',
            'crypt_type',
            'dynamic_descriptor'
        ),
        'refund' => array(
            'order_id',
            'amount',
            'txn_number',
            'crypt_type'
        ),
        'idebit_purchase' => array(
            'order_id',
            'cust_id',
            'amount',
            'idebit_track2',
            'dynamic_descriptor'
        ),
        'idebit_refund' => array(
            'order_id',
            'amount',
            'txn_number'
        ),
        'purchase_reversal' => array(
            'order_id',
            'amount'
        ),
        'ind_refund' => array(
            'order_id',
            'cust_id',
            'amount',
            'pan',
            'expdate',
            'crypt_type',
            'dynamic_descriptor'
        ),
        'preauth' => array(
            'order_id',
            'cust_id',
            'amount',
            'pan',
            'expdate',
            'crypt_type',
            'dynamic_descriptor'
        ),
        'reauth' => array(
            'order_id',
            'cust_id',
            'amount',
            'orig_order_id',
            'txn_number',
            'crypt_type'
        ),
        'completion' => array(
            'order_id',
            'comp_amount',
            'txn_number',
            'crypt_type'
        ),
        'purchasecorrection' => array(
            'order_id',
            'txn_number',
            'crypt_type'
        ),
        'opentotals' => array('ecr_number'),
        'batchclose' => array('ecr_number'),
        'cavv_purchase' => array(
            'order_id',
            'cust_id',
            'amount',
            'pan',
            'expdate',
            'cavv',
            'dynamic_descriptor'
        ),
        'cavv_preauth' => array(
            'order_id',
            'cust_id',
            'amount',
            'pan',
            'expdate',
            'cavv',
            'dynamic_descriptor'
        ),
        'card_verification' => array(
            'order_id',
            'cust_id',
            'pan',
            'expdate',
            'crypt_type'
        ),
        'recur_update' => array(
            'order_id',
            'cust_id',
            'pan',
            'expdate',
            'recur_amount',
            'add_num_recurs',
            'total_num_recurs',
            'hold',
            'terminate'
        )
    );
    var $txnArray;

    function __construct($txn)
    {

        if (is_array($txn)) {
            $txn = $txn[0];
        }

        $this->txnArray = $txn;

    }

    function toXML(){

    $tmpTxnArray=$this->txnArray;

    $txnArrayLen=count($tmpTxnArray);
    //total number of transactions

    $txnObj=$tmpTxnArray;

    $txn=$txnObj->getTransaction(); //call to a non-member function

    $txnType=array_shift($txn);
    $tmpTxnTypes=$this->txnTypes;
    $txnTypeArray=$tmpTxnTypes[$txnType];
    $txnTypeArrayLen=count($txnTypeArray); //length of a specific txn type

    $txnXMLString="";
    for($i=0;$i < $txnTypeArrayLen ;$i++)
    {
    $txnXMLString  .="
    <$txnTypeArray[$i]>
    "   //begin tag
    .$txn[$txnTypeArray[$i]] // data
    . "
    </$txnTypeArray[$i]>"; //end tag
    }

    $txnXMLString = "
    <$txnType>
    $txnXMLString";

    $recur  = $txnObj->getRecur();
    if($recur != null)
{
$txnXMLString .= $recur->toXML();
}

$avsInfo  = $txnObj->getAvsInfo();
if($avsInfo != null)
{
$txnXMLString .= $avsInfo->toXML();
}

$cvdInfo  = $txnObj->getCvdInfo();
if($cvdInfo != null)
{
$txnXMLString .= $cvdInfo->toXML();
}

$custInfo = $txnObj->getCustInfo();
if($custInfo != null)
{
$txnXMLString .= $custInfo->toXML();
}

$txnXMLString .=
"</$txnType>";

    $xmlString =$txnXMLString;

    return $xmlString;

 }//end toXML

}
