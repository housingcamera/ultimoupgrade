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

class Appmerce_EselectPlus_Model_Api_Hosted extends Appmerce_EselectPlus_Model_Api
{
    protected $_code = 'eselectplus_hosted';
    protected $_formBlockType = 'eselectplus/form_hosted';
    protected $_infoBlockType = 'eselectplus/info';

    /**
     * Generates array of fields for rehosted form
     *
     * @return array
     */
    public function getFormFields($order)
    {
        $storeId = $order->getStoreId();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress || !is_object($shippingAddress)) {
            $shippingAddress = $billingAddress;
        }

        $formFields = array();
        $formFields['ps_store_id'] = $this->getConfigData('ps_store_id', $storeId);
        $formFields['hpp_key'] = $this->getConfigData('hpp_key', $storeId);
        $formFields['charge_total'] = $this->getGrandTotal($order);
        $formFields['cust_id'] = substr($billingAddress->getLastname(), 0, 30);

        // Add Item Details Full List
        // Sum must match total amount incl tax! Think about shipping,
        // discount,...
        if ($this->getConfigData('line_item_details', $storeId)) {
            $i = 0;
            foreach ($order->getAllItems() as $orderItem) {
                if ($orderItem->getParentItemId()) {
                    continue;
                }
                ++$i;
                $formFields['id' . $i] = substr($orderItem->getItemId(), 0, 10);
                $formFields['description' . $i] = substr($orderItem->getName(), 0, 15);
                $formFields['quantity' . $i] = substr(intval($orderItem->getQtyOrdered() + 0.5), 0, 4);
                $formFields['price' . $i] = $this->getItemPrice($orderItem);
                $formFields['subtotal' . $i] = round($orderItem->getQtyOrdered() * $this->getItemPrice($orderItem), 2);
            }

            // Add shipping cost
            $shipping = $this->getShippingTotal($order);
            if ($shipping != 0) {
                ++$i;
                $formFields['id' . $i] = 'SHIPPING';
                $formFields['description' . $i] = 'SHIPPING-AMOUNT';
                $formFields['quantity' . $i] = 1;
                $formFields['price' . $i] = number_format($shipping, 2, '.', '');
                $formFields['subtotal' . $i] = number_format($shipping, 2, '.', '');
            }

            // Add discount amount
            $discount = $this->getDiscountTotal($order);
            if ($discount != 0) {
                ++$i;
                $formFields['id' . $i] = 'DISCOUNT';
                $formFields['description' . $i] = 'DISCOUNT-AMOUNT';
                $formFields['quantity' . $i] = 1;
                $formFields['price' . $i] = number_format($discount, 2, '.', '');
                $formFields['subtotal' . $i] = number_format($discount, 2, '.', '');
            }
        }

        $locale = Mage::app()->getLocale()->getLocaleCode();
        $formFields['lang'] = strpos($locale, 'fr_') !== false ? 'fr-ca' : 'en-ca';
        $formFields['order_id'] = substr($order->getIncrementId() . time(), 0, 50);
        $formFields['email'] = substr($billingAddress->getEmail(), 0, 50);

        // Actual order number as rvar
        $formFields['rvar_increment_id'] = $order->getIncrementId();

        // Transfer billing details
        if ($this->getConfigData('billing_details', $storeId)) {
            $address = $billingAddress->getStreet();
            $formFields['bill_first_name'] = substr($billingAddress->getFirstname(), 0, 30);
            $formFields['bill_last_name'] = substr($billingAddress->getLastname(), 0, 30);
            $formFields['bill_company_name'] = substr($billingAddress->getCompany(), 0, 30);
            $formFields['bill_address_one'] = substr(implode(' ', $address), 0, 30);
            $formFields['bill_city'] = substr($billingAddress->getCity(), 0, 30);
            $formFields['bill_state_or_province'] = substr($billingAddress->getRegion(), 0, 30);
            $formFields['bill_postal_code'] = substr($billingAddress->getPostcode(), 0, 30);
            $formFields['bill_country'] = substr($billingAddress->getCountry(), 0, 30);
            $formFields['bill_phone'] = substr($billingAddress->getTelephone(), 0, 30);
            $formFields['bill_fax'] = substr($billingAddress->getFax(), 0, 30);
        }

        // Transfer shipping details
        if ($this->getConfigData('shipping_details', $storeId)) {
            $address = $shippingAddress->getStreet();
            $formFields['ship_first_name'] = substr($shippingAddress->getFirstname(), 0, 30);
            $formFields['ship_last_name'] = substr($shippingAddress->getLastname(), 0, 30);
            $formFields['ship_company_name'] = substr($shippingAddress->getCompany(), 0, 30);
            $formFields['ship_address_one'] = substr(implode(' ', $address), 0, 30);
            $formFields['ship_city'] = substr($shippingAddress->getCity(), 0, 30);
            $formFields['ship_state_or_province'] = substr($shippingAddress->getRegion(), 0, 30);
            $formFields['ship_postal_code'] = substr($shippingAddress->getPostcode(), 0, 30);
            $formFields['ship_country'] = substr($shippingAddress->getCountry(), 0, 30);
            $formFields['ship_phone'] = substr($shippingAddress->getTelephone(), 0, 30);
            $formFields['ship_fax'] = substr($shippingAddress->getFax(), 0, 30);
        }

        // eFraud Support
        // Split billing address street / number (Magento stores both in 1 field)
        preg_match('/([^\d]+)\s?(.+)/i', str_replace("\n", ' ', $billingAddress->getStreet(-1)), $street);
        $formFields['avs_street_number'] = isset($street[2]) ? $street[2] : '';
        $formFields['avs_street_name'] = isset($street[1]) ? $street[1] : '';
        $formFields['avs_zipcode'] = substr($billingAddress->getPostcode(), 0, 10);
        $formFields['avs_email'] = substr($billingAddress->getEmail(), 0, 50);
        $formFields['avs_custip'] = Mage::helper('eselectplus')->getRealIpAddr();

        // Sanitize double quotes for hidden form fields
        foreach ($formFields as $key => $value) {
            if (!is_array($value)) {
                $formFields[$key] = str_replace('"', '', $value);
            }
        }

        return $formFields;
    }

    /**
     * Generates array of fields for rehosted form
     *
     * @return array
     */
    public function getVerificationFields($order, $transactionKey)
    {
        $storeId = $order->getStoreId();

        $formFields = array();
        $formFields['ps_store_id'] = $this->getConfigData('ps_store_id', $storeId);
        $formFields['hpp_key'] = $this->getConfigData('hpp_key', $storeId);
        $formFields['transactionKey'] = $transactionKey;

        return $formFields;
    }

}
