<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_ShippingAddress extends Ess_M2ePro_Model_Order_ShippingAddress
{
    //########################################

    /**
     * @return array
     */
    public function getRawData()
    {
        return array(
            'buyer_name'     => $this->order->getChildObject()->getBuyerName(),
            'recipient_name' => $this->order->getChildObject()->getBuyerName(),
            'email'          => $this->getBuyerEmail(),
            'country_id'     => $this->getData('country_code'),
            'region'         => $this->getData('state'),
            'city'           => $this->getData('city'),
            'postcode'       => $this->getPostalCode(),
            'telephone'      => $this->getPhone(),
            'company'        => $this->getData('company'),
            'street'         => array_filter($this->getData('street'))
        );
    }

    private function getBuyerEmail()
    {
        $email = $this->order->getData('buyer_email');

        if (stripos($email, 'Invalid Request') !== false || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = str_replace(' ', '-', strtolower($this->order->getChildObject()->getBuyerUserId()));
            $email .= Ess_M2ePro_Model_Magento_Customer::FAKE_EMAIL_POSTFIX;
        }

        return $email;
    }

    private function getPostalCode()
    {
        $postalCode = $this->getData('postal_code');

        if (stripos($postalCode, 'Invalid Request') !== false || $postalCode == '') {
            $postalCode = '0000';
        }

        return $postalCode;
    }

    private function getPhone()
    {
        $phone = $this->getData('phone');

        if (stripos($phone, 'Invalid Request') !== false || $phone == '') {
            $phone = '0000000000';
        }

        return $phone;
    }

    //########################################
}