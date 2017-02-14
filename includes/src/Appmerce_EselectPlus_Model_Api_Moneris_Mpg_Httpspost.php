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

class Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Httpspost
{

    var $api_token;
    var $store_id;
    var $mpgRequest;
    var $mpgResponse;

    function __construct($store_id, $api_token, $mpgRequestOBJ)
    {

        $this->store_id = $store_id;
        $this->api_token = $api_token;
        $this->mpgRequest = $mpgRequestOBJ;

        $dataToSend = $this->toXML();

        //do post

        $g = new Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Globals();
        $gArray = $g->getGlobals();

        $url = $gArray['MONERIS_PROTOCOL'] . "://" . $gArray['MONERIS_HOST'] . ":" . $gArray['MONERIS_PORT'] . $gArray['MONERIS_FILE'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToSend);
        curl_setopt($ch, CURLOPT_TIMEOUT, $gArray['CLIENT_TIMEOUT']);
        curl_setopt($ch, CURLOPT_USERAGENT, $gArray['API_VERSION']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);

        curl_close($ch);

        if (!$response) {

            $response = "<?xml version=\"1.0\"?><response><receipt>" . "<ReceiptId>Global Error Receipt</ReceiptId>" . "<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>" . "<ISO>null</ISO> <AuthCode>null</AuthCode><TransTime>null</TransTime>" . "<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>" . "<Message>null</Message><TransAmount>null</TransAmount>" . "<CardType>null</CardType>" . "<TransID>null</TransID><TimedOut>null</TimedOut>" . "</receipt></response>";
        }

        $this->mpgResponse = new Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Response($response);

    }

    function getMpgResponse()
    {
        return $this->mpgResponse;

    }

    function toXML()
    {

        $req = $this->mpgRequest;
        $reqXMLString = $req->toXML();

        $xmlString = '';
        $xmlString .= "<?xml version=\"1.0\"?>" . "<request>" . "<store_id>$this->store_id</store_id>" . "<api_token>$this->api_token</api_token>" . $reqXMLString . "</request>";

        return ($xmlString);

    }

}
