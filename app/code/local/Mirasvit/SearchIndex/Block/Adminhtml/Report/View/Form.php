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



class Mirasvit_SearchIndex_Block_Adminhtml_Report_View_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('current_model');
        $form = new Varien_Data_Form(array('id' => 'search_result_form'));
        $url = Mage::app()->getStore($model->getStoreId())->getUrl('catalogsearch/result', array(
                '_query' => array('q' => $model->getQueryText()),
            )
        );

        $fieldset = $form->addFieldset('search_term', array('legend' => $this->__('Search Term')));
        $fieldset->addField('query_text', 'link',
            array(
                'label' => $this->__('Search Query'),
                'value' => $model->getQueryText(),
                'target' => '_blank',
                'href' => $this->getUrl('*/catalog_search/edit', array('id' => $model->getId())),
            )
        );
        $fieldset->addField('num_results', 'label',
            array(
                'label' => $this->__('Number of results'),
                'value' => $model->getNumResults(),
            )
        );
        $fieldset->addField('popularity', 'label',
            array(
                'label' => $this->__('Number of uses'),
                'value' => $model->getPopularity(),
            )
        );
        $fieldset->addField('search_result_view', 'link',
            array(
                'value' => $this->__('View search results on frontend'),
                'href' => $url,
                'target' => '_blank',
            )
        );

        $this->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
