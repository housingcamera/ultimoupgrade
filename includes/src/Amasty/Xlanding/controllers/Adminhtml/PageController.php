<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Xlanding_Adminhtml_PageController extends Mage_Adminhtml_Controller_Action
{
    protected $_title     = 'Landing Page';
    protected $_modelName = 'page';
    
    protected function _setActiveMenu($menuPath)
    {
        $this->getLayout()->getBlock('menu')->setActive($menuPath);
        $this->_title($this->__('Catalog'))->_title($this->__($this->_title));     
        return $this;
    } 
    
    public function indexAction()
    {
        $this->loadLayout(); 
        $this->_setActiveMenu('catalog/amlanding/' . $this->_modelName . 's');
        $this->_addContent($this->getLayout()->createBlock('amlanding/adminhtml_' . $this->_modelName));         
		$this->renderLayout();
    }

    public function newAction()
    {
        $this->editAction();
    }
    
    public function optionsAction()
    {
		$result = '';
        
        $code = $this->getRequest()->getParam('code');
        $cond = $this->getRequest()->getParam('cond');
        if (!$code){
            $this->getResponse()->setBody($result);
            return;
        }
        
        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($code);        
        if (!$attribute){
            $this->getResponse()->setBody($result);
            return;            
        }
        
        $filterable = $attribute->getIsFilterable();
        
        if ($code == 'price') {
        	$filterable = false;
        } 
        
        $conditions = Mage::helper('amlanding')->getOperations($filterable);
        
        if (!$cond) {
        	
		$result .= $this->__('is');
        
        $result .= ' <select id="conditions" name="attr_cond[' . $code . '][]" class="select" style="width: 150px;" onchange="showOptions(this, \''. $code . '\');">';
        $result .= '<option value="">'. $this->__('Choose') . '</option>';
        foreach ($conditions as $key => $condition){
            $result .= '<option value="'.$key.'">'.$condition.'</option>';      
        }
        $result .= '</select>&nbsp;&nbsp;&nbsp;';
        
        }

        /*
         * Have condition
         */
        if ($cond && $code) {
	        if (!in_array($attribute->getFrontendInput(), array('select', 'multiselect')) ){
	        	$result .= '<input id="attr_value" name="attr_value[' . $code . '][' . $cond . '][]" value="" class="input-text" type="text" style="width: 200px;"/>';
	        } else {
		        $options = $attribute->getFrontend()->getSelectOptions();
	            $result .= '<select id="attr_value" name="attr_value[' . $code . '][' . $cond . '][]" class="select" style="width: 205px;" multiple="multiple">';	        
		        foreach ($options as $option){
		            $result .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';      
		        }
		        $result .= '</select>';    
	        }
        }
                                
        $this->getResponse()->setBody($result);
    }     
    
    public function editAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amlanding/' . $this->_modelName)->load($id);
        
        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amlanding')->__('Record does not exist'));
            $this->_redirect('adminhtml/page/');
            return;
        }
        
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        else {
            $this->prepareForEdit($model);
        }
        
        
        Mage::register('amlanding_' . $this->_modelName, $model);
        
        $this->loadLayout();
        
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        
        $this->_setActiveMenu('catalog/amlanding/' . $this->_modelName . 's');
        $this->_title($this->__('Edit'));
        
               
        $this->_addContent($this->getLayout()->createBlock('amlanding/adminhtml_' . $this->_modelName . '_edit'));
        $this->_addLeft($this->getLayout()->createBlock('amlanding/adminhtml_' . $this->_modelName . '_edit_tabs'));
        
		$this->renderLayout();
    }

    public function saveAction() 
    {
        if ($data = $this->getRequest()->getPost()) {
        	
        	
            //init model and set data
            $model = Mage::getModel('amlanding/page');

            if ($id = $this->getRequest()->getParam('id')) {
                $model->load($id);
            }
            
            $model->setData($data);
            
			$this->prepareForSave($model);
            

            //validating
            if (!$this->_validatePostData($data)) {
                $this->_redirect('adminhtml/page/edit', array('id' => $model->getPageId(), '_current' => true));
                return;
            }

            // try to save it
            try {
                // save the data
                $model->save();

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('amlanding')->__('The page has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('continue')) {
                    $this->_redirect('adminhtml/page/edit', array('id' => $model->getPageId(), '_current'=>true));
                    return;
                }
                // go to grid
                $this->_redirect('adminhtml/page/');
                return;

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('amlanding')->__('An error occurred while saving the page.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('adminhtml/page/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
        $this->_redirect('adminhtml/page/'); 
    } 
    
    public function deleteAction()
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amlanding/' . $this->_modelName)->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Record does not exist'));
            $this->_redirect('adminhtml/page/');
            return;
        }
         
        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__($this->_title . ' has been successfully deleted'));
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('adminhtml/page/');
    }    
        
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam($this->_modelName . 's');
        if (!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amlanding')->__('Please select records'));
             $this->_redirect('adminhtml/page/');
             return;
        }
        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amlanding/' . $this->_modelName)->load($id);
                $model->delete();
                // TODO remove files
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully deleted', count($ids)
                )
            );
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('adminhtml/page/');   
    }
    
    public function massActivateAction()
    {
        return $this->_modifyStatus(1);
    }
    
    public function massInactivateAction()
    {
        return $this->_modifyStatus(0);
    }     
    
    protected function _modifyStatus($status)
    {
        $ids = $this->getRequest()->getParam('pages');
        if ($ids && is_array($ids)){
            try {
                Mage::getModel('amlanding/' . $this->_modelName)->massChangeStatus($ids, $status);
                $message = $this->__('Total of %d record(s) have been updated.', count($ids));
                $this->_getSession()->addSuccess($message);
            } 
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        else {
            $this->_getSession()->addError($this->__('Please select page(s).'));
        }
        
        return $this->_redirect('adminhtml/page');
    }     
    
    /**
     * Prepare model
     * @param Amasty_Xlanding_Model_Page $model
     * @return boolean
     */
    public function prepareForSave($model)
    {
    	$request = Mage::app()->getRequest();
		$attributeValues = $request->getParam('attr_value');
		$validArray = array();
		
		if (is_array($attributeValues)) {
			foreach ($attributeValues as $index => $value) {
				if (is_array($value)) {
					foreach ($value as $cond => $options)
					$validArray[] = array(
						'code' => $index,
						'cond' => $cond,
						'value' => $options,
					);
				}
			}
		}
		
		$model->setData('attributes', serialize($validArray));
        return true;
    }
    
    public function prepareForEdit($model)
    {
        $fields = array('stores', 'cust_groups', 'cats');
        foreach ($fields as $f){
            $val = $model->getData($f);
            if (!is_array($val)){
                $model->setData($f, explode(',', $val));    
            }    
        }
        
        //$model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        return true;
    }
    
     /**
     * Validate post data
     *
     * @param array $data
     * @return bool     Return FALSE if someone item is invalid
     */
    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            /** @var $validatorCustomLayout Mage_Adminhtml_Model_LayoutUpdate_Validator */
            $validatorCustomLayout = Mage::getModel('adminhtml/layoutUpdate_validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->_getSession()->addError($message);
            }
        }
        return $errorNo;
    } 
    
    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('ambase')->isVersionLessThan(1,4)){
            return $this;
        }
        return parent::_title($text, $resetIfExists);
    }
}