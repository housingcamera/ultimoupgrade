<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Edit_Tabs_Selling extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingTemplateEditTabsSelling');
        // ---------------------------------------
    }

    //########################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // ---------------------------------------
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_edit_selling_help');
        $this->setChild('help', $helpBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'template_nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
        );
        $switcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher');
        $switcherBlock->setData($data);

        $this->setChild('selling_format', $switcherBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'template_nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
        );
        $switcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher');
        $switcherBlock->setData($data);

        $this->setChild('description', $switcherBlock);
        // ---------------------------------------

        return $this;
    }

    //########################################

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('help')
            . $this->getChildHtml('selling_format')
            . $this->getChildHtml('description')
        ;
    }

    //########################################
}