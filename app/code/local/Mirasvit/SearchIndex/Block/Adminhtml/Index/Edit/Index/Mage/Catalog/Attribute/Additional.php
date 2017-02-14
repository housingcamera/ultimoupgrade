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
 * @package   Sphinx Search Ultimate
 * @version   2.3.4
 * @build     1364
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_SearchIndex_Block_Adminhtml_Index_Edit_Index_Mage_Catalog_Attribute_Additional extends Varien_Data_Form_Element_Fieldset
{
    public function toHtml()
    {
        $model = $this->getModel();

        parent::__construct(array('legend' => Mage::helper('searchindex')->__('Attribute')));

        $attributes = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();
        $values = array();
        foreach ($attributes as $attr) {
            if (in_array($attr->getData('frontend_input'), array('select', 'multiselect'))) {
                $values[$attr->getAttributeCode()] = $attr->getFrontendLabel().' ['.$attr->getAttributeCode().']';
            }
        }

        $this->addField('attribute', 'select', array(
            'name' => 'properties[attribute]',
            'label' => Mage::helper('searchindex')->__('Attribute'),
            'required' => true,
            'value' => $model->getProperty('attribute'),
            'values' => $values,
            'after_element_html' => '[GLOBAL]',
        ));

        $this->addField('url_template', 'text', array(
            'name' => 'properties[url_template]',
            'label' => Mage::helper('searchindex')->__('Url Template'),
            'required' => true,
            'value' => $model->getProperty('url_template'),
            'after_element_html' => '[GLOBAL]',
        ));

        return parent::toHtml();
    }
}
