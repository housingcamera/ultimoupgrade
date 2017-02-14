<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Autorelated
 * @version    2.4.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Autorelated_Helper_Forms extends Mage_Core_Helper_Abstract
{
    const AW_APR_FORM_DATA_KEY = 'awautorelated_formdata';

    protected function _getFormData()
    {
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::AW_APR_FORM_DATA_KEY);
        return is_array($_formData) ? $_formData : array();
    }

    public function setFormData($data)
    {
        if (!($data instanceof Varien_Object))
            $data = new Varien_Object($data);
        $_formData = $this->_getFormData();
        if (!is_array($_formData))
            $_formData = array();
        $_formData[$data->getId() ? $data->getId() : -1] = $data;
        Mage::getSingleton('adminhtml/session')->setData(self::AW_APR_FORM_DATA_KEY, $_formData);
    }

    public function getFormData($id = null)
    {
        if (!$id)
            $id = -1;
        $_formData = $this->_getFormData();
        return $_formData && isset($_formData[$id]) ? $_formData[$id] : null;
    }

    public function unsetFormData($id = null)
    {
        if ($id === null) {
            $id = -1;
        }
        $_formData = $this->_getFormData();
        if (array_key_exists($id, $_formData)) {
            unset($_formData[$id]);
        }
        Mage::getSingleton('adminhtml/session')->setData(self::AW_APR_FORM_DATA_KEY, $_formData);
        return $this;
    }
}