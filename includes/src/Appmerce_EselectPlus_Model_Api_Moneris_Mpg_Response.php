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

class Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Response
{
    var $responseData;

    var $p;
    //parser

    var $currentTag;
    var $purchaseHash = array();
    var $refundHash;
    var $correctionHash = array();
    var $isBatchTotals;
    var $term_id;
    var $receiptHash = array();
    var $ecrHash = array();
    var $CardType;
    var $currentTxnType;
    var $ecrs = array();
    var $cards = array();
    var $cardHash = array();

    var $ACSUrl;

    function __construct($xmlString)
    {
        $this->p = xml_parser_create();
        xml_parser_set_option($this->p, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->p, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_set_object($this->p, $this);
        xml_set_element_handler($this->p, "startHandler", "endHandler");
        xml_set_character_data_handler($this->p, "characterHandler");
        xml_parse($this->p, $xmlString);
        xml_parser_free($this->p);

    }//end of constructor

    function getMpgResponseData()
    {
        return ($this->responseData);
    }

    function getAvsResultCode()
    {
        return ($this->responseData['AvsResultCode']);
    }

    function getCvdResultCode()
    {
        return ($this->responseData['CvdResultCode']);
    }

    function getCavvResultCode()
    {
        return ($this->responseData['CavvResultCode']);
    }

    function getITDResponse()
    {
        return ($this->responseData['ITDResponse']);
    }

    function getStatusCode()
    {
        return ($this->responseData['status_code']);
    }

    function getStatusMessage()
    {
        return ($this->responseData['status_message']);
    }

    function getRecurSuccess()
    {
        return ($this->responseData['RecurSuccess']);
    }

    function getCardType()
    {
        return ($this->responseData['CardType']);
    }

    function getTransAmount()
    {
        return ($this->responseData['TransAmount']);
    }

    function getTxnNumber()
    {
        return ($this->responseData['TransID']);
    }

    function getReceiptId()
    {
        return ($this->responseData['ReceiptId']);
    }

    function getTransType()
    {
        return ($this->responseData['TransType']);
    }

    function getReferenceNum()
    {
        return ($this->responseData['ReferenceNum']);
    }

    function getResponseCode()
    {
        return ($this->responseData['ResponseCode']);
    }

    function getISO()
    {
        return ($this->responseData['ISO']);
    }

    function getBankTotals()
    {
        return ($this->responseData['BankTotals']);
    }

    function getMessage()
    {
        return ($this->responseData['Message']);
    }

    function getAuthCode()
    {
        return ($this->responseData['AuthCode']);
    }

    function getComplete()
    {
        return ($this->responseData['Complete']);
    }

    function getTransDate()
    {
        return ($this->responseData['TransDate']);
    }

    function getTransTime()
    {
        return ($this->responseData['TransTime']);
    }

    function getTicket()
    {
        return ($this->responseData['Ticket']);
    }

    function getTimedOut()
    {
        return ($this->responseData['TimedOut']);
    }

    function getRecurUpdateSuccess()
    {
        return ($this->responseData['RecurUpdateSuccess']);
    }

    function getNextRecurDate()
    {
        return ($this->responseData['NextRecurDate']);
    }

    function getRecurEndDate()
    {
        return ($this->responseData['RecurEndDate']);
    }

    function getTerminalStatus($ecr_no)
    {
        return ($this->ecrHash[$ecr_no]);
    }

    function getPurchaseAmount($ecr_no, $card_type)
    {

        return ($this->purchaseHash[$ecr_no][$card_type]['Amount'] == "" ? 0 : $this->purchaseHash[$ecr_no][$card_type]['Amount']);
    }

    function getPurchaseCount($ecr_no, $card_type)
    {

        return ($this->purchaseHash[$ecr_no][$card_type]['Count'] == "" ? 0 : $this->purchaseHash[$ecr_no][$card_type]['Count']);
    }

    function getRefundAmount($ecr_no, $card_type)
    {

        return ($this->refundHash[$ecr_no][$card_type]['Amount'] == "" ? 0 : $this->refundHash[$ecr_no][$card_type]['Amount']);
    }

    function getRefundCount($ecr_no, $card_type)
    {

        return ($this->refundHash[$ecr_no][$card_type]['Count'] == "" ? 0 : $this->refundHash[$ecr_no][$card_type]['Count']);
    }

    function getCorrectionAmount($ecr_no, $card_type)
    {

        return ($this->correctionHash[$ecr_no][$card_type]['Amount'] == "" ? 0 : $this->correctionHash[$ecr_no][$card_type]['Amount']);
    }

    function getCorrectionCount($ecr_no, $card_type)
    {

        return ($this->correctionHash[$ecr_no][$card_type]['Count'] == "" ? 0 : $this->correctionHash[$ecr_no][$card_type]['Count']);
    }

    function getTerminalIDs()
    {
        return ($this->ecrs);
    }

    function getCreditCardsAll()
    {
        return (array_keys($this->cards));
    }

    function getCreditCards($ecr_no)
    {
        return ($this->cardHash[$ecr_no]);
    }

    function characterHandler($parser, $data)
    {

        if ($this->isBatchTotals) {
            switch($this->currentTag) {
                case "term_id" : {
                    $this->term_id = $data;
                    array_push($this->ecrs, $this->term_id);
                    $this->cardHash[$data] = array();
                    break;
                }

                case "closed" : {
                    $ecrHash = $this->ecrHash;
                    $ecrHash[$this->term_id] = $data;
                    $this->ecrHash = $ecrHash;
                    break;
                }

                case "CardType" : {
                    $this->CardType = $data;
                    $this->cards[$data] = $data;
                    array_push($this->cardHash[$this->term_id], $data);
                    break;
                }

                case "Amount" : {
                    if ($this->currentTxnType == "Purchase") {
                        $this->purchaseHash[$this->term_id][$this->CardType]['Amount'] = $data;
                    }
                    else if ($this->currentTxnType == "Refund") {
                        $this->refundHash[$this->term_id][$this->CardType]['Amount'] = $data;
                    }
                    
else if ($this->currentTxnType == "Correction") {
                        $this->correctionHash[$this->term_id][$this->CardType]['Amount'] = $data;
                    }
                    break;
                }

                case "Count" : {
                    if ($this->currentTxnType == "Purchase") {
                        $this->purchaseHash[$this->term_id][$this->CardType]['Count'] = $data;
                    }
                    else if ($this->currentTxnType == "Refund") {
                        $this->refundHash[$this->term_id][$this->CardType]['Count'] = $data;

                    }
                    
else if ($this->currentTxnType == "Correction") {
                        $this->correctionHash[$this->term_id][$this->CardType]['Count'] = $data;
                    }
                    break;
                }
            }
        }
        else {
            @$this->responseData[$this->currentTag] .= $data;
        }

    }//end characterHandler

    function startHandler($parser, $name, $attrs)
    {

        $this->currentTag = $name;

        if ($this->currentTag == "BankTotals") {
            $this->isBatchTotals = 1;
        }
        else if ($this->currentTag == "Purchase") {
            $this->purchaseHash[$this->term_id][$this->CardType] = array();
            $this->currentTxnType = "Purchase";
        }
        else if ($this->currentTag == "Refund") {
            $this->refundHash[$this->term_id][$this->CardType] = array();
            $this->currentTxnType = "Refund";
        }
        else if ($this->currentTag == "Correction") {
            $this->correctionHash[$this->term_id][$this->CardType] = array();
            $this->currentTxnType = "Correction";
        }

    }

    function endHandler($parser, $name)
    {

        $this->currentTag = $name;
        if ($name == "BankTotals") {
            $this->isBatchTotals = 0;
        }

        $this->currentTag = "/dev/null";
    }

}
