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



class Mirasvit_SearchIndex_Block_Adminhtml_Index_Edit_Index_External_Database extends Varien_Data_Form_Element_Fieldset
{
    public function toHtml()
    {
        $model = $this->getModel();

        parent::__construct(array('legend' => Mage::helper('searchindex')->__('Database Settings')));

        $this->addField('db_connection_name', 'text', array(
            'name' => 'properties[db_connection_name]',
            'label' => Mage::helper('searchindex')->__('Database Connection Name'),
            'required' => true,
            'value' => $model->getProperty('db_connection_name') ? $model->getProperty('db_connection_name') : 'default_setup',
            'note' => Mage::helper('searchindex/help')->field('db_connection_name'),
            'after_element_html' => '<span style="margin-left:25px">[GLOBAL]</span>',
        ));

        $this->addField('db_table_prefix', 'text', array(
            'name' => 'properties[db_table_prefix]',
            'label' => Mage::helper('searchindex')->__('Table Prefix'),
            'required' => false,
            'value' => $model->getProperty('db_table_prefix'),
            'note' => Mage::helper('searchindex/help')->field('db_table_prefix'),
            'after_element_html' => '<span style="margin-left:25px">[GLOBAL]</span>',
        ));

        return parent::toHtml();
    }
}
