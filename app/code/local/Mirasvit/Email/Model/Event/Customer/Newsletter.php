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



class Mirasvit_Email_Model_Event_Customer_Newsletter extends Mirasvit_Email_Model_Event_Abstract
{
    const EVENT_CODE = 'customer_newsletter|';

    public function getEventsGroup()
    {
        return Mage::helper('email')->__('Customer');
    }

    public function getEvents()
    {
        $result = array();

        $result[self::EVENT_CODE.'subscribed'] = Mage::helper('email')->__('Newsletter subscription');
        $result[self::EVENT_CODE.'unsubscribed'] = Mage::helper('email')->__('Newsletter unsubscription');
        $result[self::EVENT_CODE.'subscription_status_changed'] = Mage::helper('email')->__('Newsletter subscription status change');

        return $result;
    }

    public function findEvents($eventCode, $from)
    {
        return array();
    }

    public function observer($eventCode, $observer)
    {
        if (!Mage::helper('email/event')->isEventObserved($eventCode)) {
            return $this;
        }

        $subscriber = $observer->getDataObject();
        $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());

        $event = array(
            'time' => time(),
            'customer_email' => $subscriber->getSubscriberEmail(),
            'customer_name' => $customer->getName(),
            'customer_id' => $customer->getId(),
            'store_id' => $subscriber->getStoreId(),
            'subscription_status' => $this->getStatusLabelByCode($subscriber->getSubscriberStatus()),
        );

        $this->saveEvent($eventCode, $this->getEventUniqKey($event), $event);

        return $this;
    }

    public function getStatusLabelByCode($statusCode)
    {
        $options = Mage::getModel('email/system_source_subscriptionStatus')->toArray();

        return $options[$statusCode];
    }
}
