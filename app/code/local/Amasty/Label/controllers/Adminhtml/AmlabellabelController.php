<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Adminhtml_AmlabellabelController extends Mage_Adminhtml_Controller_Action
{
    protected $_title     = 'Product label';
    protected $_modelName = 'label';

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/amlabel');
        $this->_addContent($this->getLayout()->createBlock('amlabel/adminhtml_' . $this->_modelName));
        $this->renderLayout();
    }

    protected function _setActiveMenu($menuPath)
    {
        $this->getLayout()->getBlock('menu')->setActive($menuPath);
        $this->_title($this->__('Catalog'))->_title($this->__(ucwords($this->_title) . 's'));

        return $this;
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('ambase')->isVersionLessThan(1, 4)) {
            return $this;
        }

        return parent::_title($text, $resetIfExists);
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function editAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        $model = Mage::getModel('amlabel/' . $this->_modelName)->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amlabel')->__('Record does not exist'));
            $this->_redirect('*/*/');

            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        } else {
            $this->prepareForEdit($model);
        }

        Mage::register('amlabel_' . $this->_modelName, $model);

        $this->loadLayout();

        $this->_setActiveMenu('catalog/amlabel');
        $this->_title($this->__('Edit'));

        $content = $this->getLayout()->createBlock('amlabel/adminhtml_' . $this->_modelName . '_edit');
        $this->_addContent($content);
        $this->_addLeft($this->getLayout()->createBlock('amlabel/adminhtml_' . $this->_modelName . '_edit_tabs'));

        $this->renderLayout();
    }

    protected function prepareForEdit($model)
    {
        $stores = $model->getData('stores');
        if (!is_array($stores)) {
            $model->setData('stores', explode(',', $stores));
        }

        $customerGroups = $model->getData('customer_groups');
        if (!is_array($customerGroups)) {
            $model->setData('customer_groups', explode(',', $customerGroups));
        }

        $categories = $model->getData('category');
        if (!is_array($categories)) {
            $model->setData('category', explode(',', $categories));
        }

        if ($model->getData('attr_multi')) {
            $attrValue = $model->getData('attr_value');
            $model->setData('attr_value', explode(',', $attrValue));
        }

        return true;
    }

    public function saveAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('amlabel/' . $this->_modelName);
        $data  = $this->getRequest()->getPost();

        if ($data) {
            $openTab = $data['open_tab_input'];
            if ($openTab) {
                Mage::getSingleton('core/cookie')->set('amasty_open_tab_input', $openTab ,time()+86400,'/');
            }
            $data = $this->_filterDates($data, array('from_date', 'to_date'));
            if (!empty($data['to_time'])) {
                $data['to_date'] = $data['to_date'] . ' ' . $data['to_time'];
            }

            if (!empty($data['from_time'])) {
                $data['from_date'] = $data['from_date'] . ' ' . $data['from_time'];
            }

            $data = $this->_saveColorSize($data);
            $model->setData($data)->setId($id);
            try {
                $this->prepareForSave($model);

                $model->save();

                if ($model->getIncludeSku()) {
                    $this->_checkAttribute('sku');
                }

                if ($model->getAttrCode()) {
                    $this->_checkAttribute($model->getAttrCode());
                }

                $auto = false;
                if (!$model->getProdImageWidth()
                    || !$model->getProdImageHeight()
                    || !$model->getCatImageWidth()
                    || !$model->getCatImageHeight()) {
                    $auto = $this->_checkAuto($model->getData('stores'));
                }


                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $msg = Mage::helper('amlabel')->__($this->_title . ' has been successfully saved');
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
                if ($this->getRequest()->getParam('continue')
                    || $auto) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*');
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id));
            }

            return;
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amlabel')->__('Unable to find a record to save'));
        $this->_redirect('*/*');
    }

    protected function prepareForSave($model)
    {
        // convert stores from array to string
        $stores = $model->getData('stores');
        if (is_array($stores)) {
            // need commas to simplify sql query
            $model->setData('stores', ',' . implode(',', $stores) . ',');
        } else { // need for null value
            $model->setData('stores', '');
        }

        if ($model->getProdImageWidth() > 100 || $model->getProdImageHeight() > 100 || $model->getCatImageWidth() > 100 || $model->getCatImageHeight() > 100) {
            $msg = Mage::helper('amlabel')->__('Label Size value must be between 1 and 100.');
            Mage::getSingleton('adminhtml/session')->addError($msg);
        }

        $customerGroups = $model->getData('customer_groups');
        if (is_array($customerGroups)) {
            $model->setData('customer_groups', implode(',', $customerGroups));
        } else {
            $model->setData('customer_groups', '');
        }

        $categories = $model->getData('category');
        if (is_array($categories)) {
            $model->setData('category', implode(',', $categories));
        } else {
            $model->setData('category', '');
        }

        $attrCode  = $model->getData('attr_code');
        if ($attrCode) {
            $attrValue = $model->getData('attr_value');
            if (is_array($attrValue)) {
                $model->setData('attr_value', implode(',', $attrValue));
            } else {
                $model->setData('attr_value', $attrValue);
            }
        } else {
            $model->setData('attr_value', '');
            $model->setData('attr_multi', 0);
            $model->setData('attr_rule', 0);
        }

        //upload images
        $data        = $this->getRequest()->getPost();
        $path        = Mage::getBaseDir('media') . DS . 'amlabel' . DS;
        $imagesTypes = array('prod', 'cat');
        foreach ($imagesTypes as $type) {
            $field = 'label_type' . $type . '_img';
            if ($data[$field] == 'download' . $type . '_img') {
                $field = $type . '_img';

                $isRemove = array_key_exists('remove_' . $field, $data);
                $hasNew = !empty($_FILES[$field]['name']);

                try {
                    // remove the old file
                    if ($isRemove || $hasNew) {
                        $oldName = isset($data['old_' . $field]) ? $data['old_' . $field] : '';
                        if ($oldName) {
                            $model->setData($field, '');
                        }
                    }

                    // upload a new if any
                    if (!$isRemove && $hasNew) {
                        //find the first available name
                        $newName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_FILES[$field]['name']);
                        if (substr($newName, 0, 1) == '.') // all non-english symbols
                            $newName = 'label' . $newName;
                        $i = 0;
                        while (file_exists($path . $newName)) {
                            $newName = $i . $newName;
                            ++$i;
                        }

                        $uploader = new Varien_File_Uploader($field);
                        $uploader->setFilesDispersion(false);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setAllowedExtensions(array('png', 'gif', 'jpg', 'jpeg'));
                        $uploader->save($path, $newName);

                        $model->setData($field, $newName);
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            else{
                $field = 'shape_type' . $type . '_img';
                if(array_key_exists($field, $data) && $data[$field]) {
                    $shape = $data[$field];
                    $color = $type . '_label_color';
                    $color = $data[$color];
                    $fileName = Amasty_Label_Model_Shape::generateNewLabel($shape, $color);
                    $field = $type . '_img';
                    $model->setData($field, $fileName);
                }
            }
        }

        return true;
    }

    protected function _checkAttribute($code)
    {
        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode(Mage::getResourceModel('catalog/product')->getTypeId(), $code);
        if (!$attribute->getUsedInProductListing()) {
            Mage::log('Check the `Used in Product Listing` property of the `' . $attribute->getFrontendLabel() . '` product attribute', null, 'Amasty_Label.log', true);
            $url = Mage::getModel('adminhtml/url')->getUrl('adminhtml/catalog_product_attribute/edit', array('attribute_id' => $attribute->getId()));
            $msg = Mage::helper('amlabel')->__(
                'You need set to `Yes` the `Used in Product Listing` property of the `%s` product attribute <a href="%s" target="_blank">here</a>',
                $attribute->getFrontendLabel(), $url
            );
            Mage::getSingleton('adminhtml/session')->addError($msg);
        }

        return true;
    }

    public function deleteAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        $model = Mage::getModel('amlabel/' . $this->_modelName)->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Record does not exist'));
            $this->_redirect('*/*/');

            return;
        }

        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__($this->_title . ' has been successfully deleted')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }

    public function duplicateAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        $model = Mage::getModel('amlabel/' . $this->_modelName)->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Record does not exist'));
            $this->_redirect('*/*/');

            return;
        }

        try {
            $model->setId(null);
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__($this->_title . ' has been successfully duplicated')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam($this->_modelName . 's');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amlabel')->__('Please select records'));
            $this->_redirect('*/*/');

            return;
        }

        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amlabel/' . $this->_modelName)->load($id);
                $model->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully deleted', count($ids)
                )
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');

    }

    public function masschangestatusAction()
    {
        $ids = $this->getRequest()->getParam($this->_modelName . 's');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amlabel')->__('Please select records'));
            $this->_redirect('*/*/');

            return;
        }

        $status = $this->getRequest()->getParam('amlabel_value');
        if (!($status == "1" || $status == "0")) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amlabel')->__('Please select status'));
            $this->_redirect('*/*/');

            return;
        }

        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amlabel/' . $this->_modelName)->load($id);
                $model->setIsActive($status);
                $model->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('amlabel')->__(
                    'Total of %d record(s) were successfully updated', count($ids)
                )
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');

    }

    public function optionsAction()
    {
        $result = '<input id="attr_value" name="attr_value" value="" class="input-text" type="text" />';

        $code = $this->getRequest()->getParam('code');
        if (!$code) {
            $this->getResponse()->setBody($result);

            return;
        }

        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($code);
        if (!$attribute) {
            $this->getResponse()->setBody($result);

            return;
        }

        $dropdowns = array('select', 'multiselect', 'boolean');
        if (!in_array($attribute->getFrontendInput(), $dropdowns)) {
            $this->getResponse()->setBody($result);

            return;
        }

        $options = $attribute->getFrontend()->getSelectOptions();

        $multiple = $this->getRequest()->getParam('multiple');
        if ($multiple) {
            $result = '<select id="attr_value" name="attr_value[]" size="10" class=" select multiselect" multiple="multiple">';
        } else {
            $result = '<select id="attr_value" name="attr_value" class="select">';
        }

        foreach ($options as $option) {
            $result .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }
        $result .= '</select>';

        $this->getResponse()->setBody($result);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/amlabel');
    }

    protected function _checkAuto($stores)
    {
        return false;
    }

    protected function _saveColorSize($data){
        $catStyles = $data['cat_style'];
        if(array_key_exists('cat_size', $data) && $data['cat_size']){
            $size = 'font-size: ' . $data['cat_size'] . ';';
            if(strpos($catStyles, 'font-size') !== FALSE) {
                $catStyles = preg_replace("@font-size(.*?);@s", $size, $catStyles);
            }
            else{
                $catStyles .= $size;
            }
        }
        if(array_key_exists('cat_color', $data) && $data['cat_color']){
            $color = 'color: #' . $data['cat_color'] . ';';
            if(strpos($catStyles, 'color:') !== FALSE) {
                $catStyles = preg_replace("@color(.*?);@s", $color, $catStyles);
            }
            else{
                $catStyles .= $color;
            }
        }

        $catStyles = str_replace(";;", ";", $catStyles);
        $data['cat_style'] = $catStyles;

        $prodStyles = $data['prod_style'];
        if(array_key_exists('prod_size', $data) && $data['prod_size']){
            $size = 'font-size: ' . $data['prod_size'] . ';';
            if(strpos($prodStyles, 'font-size') !== FALSE) {
                $prodStyles = preg_replace("@font-size(.*?);@s", $size, $prodStyles);
            }
            else{
                $prodStyles .= $size;
            }
        }
        if(array_key_exists('prod_color', $data) && $data['prod_color']){
            $color = 'color: #' . $data['prod_color'] . ';';
            if(strpos($prodStyles, 'color:') !== FALSE) {
                $prodStyles = preg_replace("@color(.*?);@s", $color, $prodStyles);
            }
            else{
                $prodStyles .= $color;
            }
        }

        $prodStyles = str_replace(";;", ";", $prodStyles);
        $data['prod_style'] = $prodStyles;

        return $data;
    }
}
