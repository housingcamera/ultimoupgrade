<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Follow Up Email
 * @version   1.0.14
 * @build     630
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Email_Model_System_Source_SubscriptionStatus
{
    public static function toArray()
    {
        $result = array(
            Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED => Mage::helper('email')->__('Subscribed'),
            Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE => Mage::helper('email')->__('Not Activated'),
            Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => Mage::helper('email')->__('Unsubscribed'),
            Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED => Mage::helper('email')->__('Unconfirmed'),
        );

        return $result;
    }

    public static function toOptionArray()
    {
        $options = self::toArray();
        $result = array();

        foreach ($options as $key => $value) {
            $result[] = array(
                'value' => $key,
                'label' => $value,
            );
        }

        return $result;
    }
}
