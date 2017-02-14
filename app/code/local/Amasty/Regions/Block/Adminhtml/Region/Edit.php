<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */
class Amasty_Regions_Block_Adminhtml_Region_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'amregions';
        $this->_controller = 'adminhtml_region';

        $this->_addButton('save_and_continue', array(
            'label'     => $this->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class' => 'save'
        ), 10);
        $this->_updateButton('save', 'onclick', 'if(editForm.submit())disableButtons();');


        $this->_formScripts[] = "function saveAndContinueEdit(){ if(editForm.submit($('edit_form').action + 'continue/edit'))disableButtons();}";

        $this->_formScripts[] = "function disableButtons(){
            $$('.form-buttons button').each(function(btn){
        btn.disabled = true; $(btn).addClassName('disabled');});
        }";
    }

    public function getHeaderText()
    {
        $model = Mage::registry('amregions_region');
        if ($model->getId()){
            $header = $this->__('Edit Shipping Area `%s`', $model->getRegionTitle());
        } else {
            $header = $this->__('New Shipping Area');
        }
        return $header;
    }
}