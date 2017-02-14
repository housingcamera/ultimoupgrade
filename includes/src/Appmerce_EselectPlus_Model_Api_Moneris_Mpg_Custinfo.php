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

class Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Custinfo
{

    var $level3template = array('cust_info' => array(
            'email',
            'instructions',
            'billing' => array(
                'first_name',
                'last_name',
                'company_name',
                'address',
                'city',
                'province',
                'postal_code',
                'country',
                'phone_number',
                'fax',
                'tax1',
                'tax2',
                'tax3',
                'shipping_cost'
            ),
            'shipping' => array(
                'first_name',
                'last_name',
                'company_name',
                'address',
                'city',
                'province',
                'postal_code',
                'country',
                'phone_number',
                'fax',
                'tax1',
                'tax2',
                'tax3',
                'shipping_cost'
            ),
            'item' => array(
                'name',
                'quantity',
                'product_code',
                'extended_amount'
            )
        ));

    var $level3data;
    var $email;
    var $instructions;

    function __construct($custinfo = 0, $billing = 0, $shipping = 0, $items = 0)
    {
        if ($custinfo) {
            $this->setCustInfo($custinfo);
        }
    }

    function setCustInfo($custinfo)
    {
        $this->level3data['cust_info'] = array($custinfo);
    }

    function setEmail($email)
    {

        $this->email = $email;
        $this->setCustInfo(array(
            'email' => $email,
            'instructions' => $this->instructions
        ));
    }

    function setInstructions($instructions)
    {

        $this->instructions = $instructions;
        $this->setCustinfo(array(
            'email' => $this->email,
            'instructions' => $instructions
        ));
    }

    function setShipping($shipping)
    {
        $this->level3data['shipping'] = array($shipping);
    }

    function setBilling($billing)
    {
        $this->level3data['billing'] = array($billing);
    }

    function setItems($items)
    {
        if (!isset($this->level3data['item'])) {
            $this->level3data['item'] = array($items);
        }
        else {
            $index = count($this->level3data['item']);
            $this->level3data['item'][$index] = $items;
        }
    }

    function toXML()
    {
        $xmlString = $this->toXML_low($this->level3template, "cust_info");
        return $xmlString;
    }

    function toXML_low($template, $txnType)
    {

        for ($x = 0; $x < count($this->level3data[$txnType]); $x++) {
            if ($x > 0) {
                $xmlString .= "</$txnType><$txnType>";
            }
            $keys = array_keys($template);
            for ($i = 0; $i < count($keys); $i++) {
                $tag = $keys[$i];

                if (is_array($template[$keys[$i]])) {
                    $data = $template[$tag];

                    if (!count($this->level3data[$tag])) {
                        continue;
                    }
                    $beginTag = "<$tag>";
                    $endTag = "</$tag>";

                    $xmlString .= $beginTag;
                    if (is_array($data)) {
                        $returnString = $this->toXML_low($data, $tag);
                        $xmlString .= $returnString;
                    }
                    $xmlString .= $endTag;
                }
                else {
                    $tag = $template[$keys[$i]];
                    $beginTag = "<$tag>";
                    $endTag = "</$tag>";
                    $data = $this->level3data[$txnType][$x][$tag];

                    $xmlString .= $beginTag . $data . $endTag;
                }

            }//end inner for

        }//end outer for

        return $xmlString;
    }//end toXML_low

}
