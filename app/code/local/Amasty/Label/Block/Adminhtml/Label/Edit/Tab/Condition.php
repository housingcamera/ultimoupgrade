<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */ 
class Amasty_Label_Block_Adminhtml_Label_Edit_Tab_Condition extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Label_Helper_Data */
        $hlp = Mage::helper('amlabel');
        
        $fldProd = $form->addFieldset('products', array('legend'=> $hlp->__('Individual Products')));
        $fldProd->addField('include_type', 'select', array(
            'label'     => $hlp->__('Apply label to'),
            'name'      => 'include_type',
            'values'    => array(
                0 => $hlp->__('All matching products and SKUs listed below'), 
                1 => $hlp->__('All matching products except SKUs listed below'), 
                2 => $hlp->__('SKUs listed below only'), 
             ),              
        ));         
        $fldProd->addField('include_sku', 'text', array(
            'label'     => $hlp->__('SKUs'),
            'name'      => 'include_sku',
            'note'      => $hlp->__('Comma separated SKUs, no spaces. Please make sure the SKU attribute properties `Visible on Product View Page on Front-end`, `Used in Product Listing` are set to `Yes`'),
        ));

        $fldDateRange = $form->addFieldset('timeline', array('legend'=> $hlp->__('Date Range')));
        $dateEnabled = $fldDateRange->addField('date_range_enabled', 'select', array(
            'label'     => $hlp->__('Use Date Range'),
            'title'     => $hlp->__('Use Date Range'),
            'name'      => 'date_range_enabled',
            'options'   => array(
                '0' => $hlp->__('No'),
                '1' => $hlp->__('Yes'),
            ),
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fromDate = $fldDateRange->addField('from_date', 'date', array(
            'name'   => 'from_date',
            'label'  => $hlp->__('From Date'),
            'title'  => $hlp->__('From Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' =>  Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso,
        ));

        $fromTime = $fldDateRange->addField('from_time', 'text', array(
            'name'   => 'from_time',
            'label'  => $hlp->__('From Time'),
            'title'  => $hlp->__('From Time'),
            'note'      => $hlp->__('In format 15:32'),
        ));

        $toDate = $fldDateRange->addField('to_date', 'date', array(
            'name'   => 'to_date',
            'label'  => $hlp->__('To Date'),
            'title'  => $hlp->__('To Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' =>  Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
        $toTime = $fldDateRange->addField('to_time', 'text', array(
            'name'   => 'to_time',
            'label'  => $hlp->__('To Time'),
            'title'  => $hlp->__('To Time'),
            'note'      => $hlp->__('In format 19:32'),
        ));
        
        $fldState = $form->addFieldset('state', array('legend'=> $hlp->__('State')));
        $fldState->addField('is_new', 'select', array(
            'label'     => $hlp->__('Is New'),
            'name'      => 'is_new',
            'values'    => array(
                0 => $hlp->__('Does not matter'), 
                1 => $hlp->__('No'), 
                2 => $hlp->__('Yes'), 
             ),
        ));
        $isSale = $fldState->addField('is_sale', 'select', array(
            'label'     => $hlp->__('Is on Sale'),
            'name'      => 'is_sale',
            'values'    => array(
                0 => $hlp->__('Does not matter'), 
                1 => $hlp->__('No'), 
                2 => $hlp->__('Yes'), 
             ),
        ));
        $specialPriceOnly = $fldState->addField('special_price_only', 'select', array(
            'label'     => $hlp->__('Use Special Price Only'),
            'name'      => 'special_price_only',
            'note'      => $hlp->__('For `On Sale` condition'),
            'values'    => array(
                0 => $hlp->__('No'),
                1 => $hlp->__('Yes'),
            ),
        ));

        $fldCat = $form->addFieldset('cat', array('legend'=> $hlp->__('Categories')));
        $fldCat->addField('category', 'multiselect', array(
            'label'     => $hlp->__('Categories'),
            'name'      => 'category[]',
            'values'    => $this->getTree(),
        ));

        $fldAttr = $form->addFieldset('attr', array('legend'=> $hlp->__('Attribute')));
        $attributes = $this->getAttributes();
        $attrCode = $fldAttr->addField('attr_code', 'select', array(
            'label'     => $hlp->__('Has attribute'),
            'name'      => 'attr_code',
            'values'    => $attributes,
            'onchange'  => 'showOptions(this)',
            'note'      => $hlp->__('If you do not see the label, please make sure the attribute properties `Visible on Product View Page on Front-end`, `Used in Product Listing` are set to `Yes`'),
        ));

        $attrMulti = $fldAttr->addField('attr_multi', 'select', array(
            'label'    => $hlp->__('Use Multiselect'),
            'title'    => $hlp->__('Use Multiselect'),
            'name'     => 'attr_multi',
            'onchange' => 'showOptions($(\'attr_code\'))',
            'options'   => array(
                '0' => $hlp->__('No'),
                '1' => $hlp->__('Yes'),
            ),
        ));

        $attrRule = $fldAttr->addField('attr_rule', 'select', array(
            'label'    => $hlp->__('Rule'),
            'title'    => $hlp->__('Rule'),
            'name'     => 'attr_rule',
            'options'   => array(
                '0' => $hlp->__('One of selected values'),
                '1' => $hlp->__('All selected values'),
            ),
        ));

        $attributeCode = Mage::registry('amlabel_label')->getData('attr_code');
        if (('' != $attributeCode) && ($attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode))) {
            $dropdowns = array('select', 'multiselect', 'boolean');
            if (in_array($attribute->getFrontendInput(), $dropdowns)) {
                $options = $attribute->getFrontend()->getSelectOptions();
                if (Mage::registry('amlabel_label')->getData('attr_multi')) {
                    $attrValue = $fldAttr->addField('attr_value', 'multiselect', array(
                        'label'     => $hlp->__('Attribute value is'),
                        'name'      => 'attr_value',
                        'values'    => $options,
                    ));
                } else {
                    $attrValue = $fldAttr->addField('attr_value', 'select', array(
                        'label'     => $hlp->__('Attribute value is'),
                        'name'      => 'attr_value',
                        'values'    => $options,
                    ));
                }
            } else {
                $attrValue = $fldAttr->addField('attr_value', 'text', array(
                  'label'     => $hlp->__('Attribute value is'),
                  'name'      => 'attr_value',
                ));
            }
        } else {
            $attrValue = $fldAttr->addField('attr_value', 'text', array(
                'label'     => $hlp->__('Attribute value is'),
                'name'      => 'attr_value',
            ));
        }

        $fldStock = $form->addFieldset('stock', array('legend'=> $hlp->__('Stock')));
        $fldStock->addField('stock_status', 'select', array(
            'label'     => $hlp->__('Status'),
            'name'      => 'stock_status',
            'values'    => array(
                0 => $hlp->__('Does not matter'), 
                1 => $hlp->__('Out of Stock'), 
                2 => $hlp->__('In Stock'), 
             ),
        ));
        $stockEnabled = $fldStock->addField('product_stock_enabled', 'select', array(
            'label'     => $hlp->__('Use Stock Range'),
            'title'     => $hlp->__('Use Stock Range'),
            'name'      => 'product_stock_enabled',
            'options'   => array(
                '0' => $hlp->__('No'),
                '1' => $hlp->__('Yes'),
            ),
        ));
        $stockRange = $fldStock->addField('stock_less', 'text', array(
            'label'  => $hlp->__('Display if stock is lower than '),
            'title'  => $hlp->__('Display if stock is lower than '),
            'name'   => 'stock_less'
        ));

        $fldPriceRange = $form->addFieldset('price', array('legend'=> $hlp->__('Price Range')));
        $priceEnabled = $fldPriceRange->addField('price_range_enabled', 'select', array(
            'label'     => $hlp->__('Use Price Range'),
            'title'     => $hlp->__('Use Price Range'),
            'name'      => 'price_range_enabled',
            'options'   => array(           
                '0' => $hlp->__('No'),
                '1' => $hlp->__('Yes'),
            ),
        ));
        
        $byPrice = $fldPriceRange->addField('by_price', 'select', array(
            'label'     => $hlp->__('By Price'),
            'title'     => $hlp->__('By Price'),
            'name'      => 'by_price',
            'options'   => array(           
                '0' => $hlp->__('Base Price'),
                '1' => $hlp->__('Special Price'),
                '2' => $hlp->__('Final Price'),
                '3' => $hlp->__('Final Price Incl Tax'),
                '4' => $hlp->__('Starting from Price'),
                '5' => $hlp->__('Starting to Price'),
            ),
        ));
        
        $fromPrice = $fldPriceRange->addField('from_price', 'text', array(
            'name'   => 'from_price',
            'label'  => $hlp->__('From Price'),
            'title'  => $hlp->__('From Price'),
        ));
		
        $toPrice = $fldPriceRange->addField('to_price', 'text', array(
            'name'   => 'to_price',
            'label'  => $hlp->__('To Price'),
            'title'  => $hlp->__('To Price'),
        ));
        
        $fldGroup = $form->addFieldset('customer_group', array('legend'=> $hlp->__('Customer Groups')));
        $groupEnabled = $fldGroup->addField('customer_group_enabled', 'select', array(
            'label'     => $hlp->__('Use Customer Groups'),
            'title'     => $hlp->__('Use Customer Groups'),
            'name'      => 'customer_group_enabled',
            'options'   => array(           
                '0' => $hlp->__('No'),
                '1' => $hlp->__('Yes'),
            ),
        ));
        $groups = $fldGroup->addField('customer_groups', 'multiselect', array(
            'label'  => $hlp->__('For Customer Groups'),
            'title'  => $hlp->__('For Customer Groups'),
            'name'   => 'customer_groups[]',
            'values' => Mage::getResourceModel('customer/group_collection')->load()->toOptionArray(),
        ));
       
        $data = Mage::registry('amlabel_label')->getData();
		if ($data) {
			$data['is_active'] = '1';
			
			if (isset($data['from_date'])) {
				$dateFrom = explode(" ", $data['from_date']);
				
				if (isset($dateFrom[1]) && $dateFrom[1] != '00:00:00') {
					$data['from_time'] = $dateFrom[1];
				}
			}
			
			if (isset($data['to_date'])) {
				$dateTo = explode(" ", $data['to_date']);
				
				if (isset($dateTo[1]) && $dateTo[1] != '00:00:00') {
					$data['to_time'] = $dateTo[1];
				}
			}
			
			//set form values
			$form->setValues($data);
		}

        $dependencies = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            // Customer Groups
            ->addFieldMap($groupEnabled->getHtmlId(), $groupEnabled->getName())
            ->addFieldMap($groups->getHtmlId(), $groups->getName())
            ->addFieldDependence(
                $groups->getName(),
                $groupEnabled->getName(),
                '1'
            ) // Price Range
            ->addFieldMap($priceEnabled->getHtmlId(), $priceEnabled->getName())
            ->addFieldMap($byPrice->getHtmlId(), $byPrice->getName())
            ->addFieldMap($fromPrice->getHtmlId(), $fromPrice->getName())
            ->addFieldMap($toPrice->getHtmlId(), $toPrice->getName())
            ->addFieldDependence(
                $byPrice->getName(),
                $priceEnabled->getName(),
                '1'
            )
            ->addFieldDependence(
                $fromPrice->getName(),
                $priceEnabled->getName(),
                '1'
            )
            ->addFieldDependence(
                $toPrice->getName(),
                $priceEnabled->getName(),
                '1'
            ) // Is on Sale
            ->addFieldMap($isSale->getHtmlId(), $isSale->getName())
            ->addFieldMap($specialPriceOnly->getHtmlId(), $specialPriceOnly->getName())
            ->addFieldDependence(
                $specialPriceOnly->getName(),
                $isSale->getName(),
                '2'
            ) // Date Range
            ->addFieldMap($dateEnabled->getHtmlId(), $dateEnabled->getName())
            ->addFieldMap($fromDate->getHtmlId(), $fromDate->getName())
            ->addFieldMap($fromTime->getHtmlId(), $fromTime->getName())
            ->addFieldMap($toDate->getHtmlId(), $toDate->getName())
            ->addFieldMap($toTime->getHtmlId(), $toTime->getName())
            ->addFieldDependence(
                $fromDate->getName(),
                $dateEnabled->getName(),
                '1'
            )
            ->addFieldDependence(
                $fromTime->getName(),
                $dateEnabled->getName(),
                '1'
            )
            ->addFieldDependence(
                $toDate->getName(),
                $dateEnabled->getName(),
                '1'
            )
            ->addFieldDependence(
                $toTime->getName(),
                $dateEnabled->getName(),
                '1'
            )
            ->addFieldMap($attrMulti->getHtmlId(), $attrMulti->getName())
            ->addFieldMap($attrRule->getHtmlId(), $attrRule->getName())
            ->addFieldDependence(
                $attrRule->getName(),
                $attrMulti->getName(),
                '1'
            )
            ->addFieldMap($stockEnabled->getHtmlId(), $stockEnabled->getName())
            ->addFieldMap($stockRange->getHtmlId(), $stockRange->getName())
            ->addFieldDependence(
                $stockRange->getName(),
                $stockEnabled->getName(),
                '1'
            );

        if (version_compare(Mage::getVersion(), '1.7.0.2', '>=')) { // Dependency from multiple values is not implemented.
            $codes = array_keys($attributes);
            array_shift($codes);
            $dependencies
                ->addFieldMap($attrCode->getHtmlId(), $attrCode->getName())
                ->addFieldMap($attrValue->getHtmlId(), $attrValue->getName())
                ->addFieldMap($attrMulti->getHtmlId(), $attrMulti->getName())
                ->addFieldDependence(
                    $attrMulti->getName(),
                    $attrCode->getName(),
                    $codes
                )
                ->addFieldDependence(
                    $attrValue->getName(),
                    $attrCode->getName(),
                    $codes
                )
                ->addFieldDependence(
                    $attrRule->getName(),
                    $attrCode->getName(),
                    $codes
                );
        }

        $this->setChild('form_after', $dependencies);

        return parent::_prepareForm();
    } 
    
    protected function getAttributes()
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId())
        ;
            
        $options = array(''=>'');
		foreach ($collection as $attribute){
		    $label = $attribute->getFrontendLabel();
			if ($label){ // skip system attributes
			    $options[$attribute->getAttributeCode()] = $label;
			}
		}
		asort($options);
        
		return $options;
    }

    /**
     * Genarates tree of all categories
     *
     * @return array sorted list category_id=>title
     */
    protected function getTree()
    {
        $rootId = Mage::app()->getStore(0)->getRootCategoryId();         
        $tree = array();
        
        $collection = Mage::getModel('catalog/category')
            ->getCollection()->addNameToResult();
        
        $pos = array();
        foreach ($collection as $cat){
            $path = explode('/', $cat->getPath());
            if ((!$rootId || in_array($rootId, $path)) && $cat->getLevel()){
                $tree[$cat->getId()] = array(
                    'label' => str_repeat('--', $cat->getLevel()) . $cat->getName(), 
                    'value' => $cat->getId(),
                    'path'  => $path,
                );
            }
            $pos[$cat->getId()] = $cat->getPosition();
        }
        
        foreach ($tree as $catId => $cat){
            $order = array();
            foreach ($cat['path'] as $id){
            	if (isset($pos[$id])){
                	$order[] = $pos[$id];
            	}
            }
            $tree[$catId]['order'] = $order;
        }
        
        usort($tree, array($this, 'compare'));
        array_unshift($tree, array('value'=>'', 'label'=>''));
        
        return $tree;
    }
    
    /**
     * Compares category data. Must be public as used as a callback value
     *
     * @param array $a
     * @param array $b
     * @return int 0, 1 , or -1
     */
    public function compare($a, $b)
    {
        foreach ($a['path'] as $i => $id){
            if (!isset($b['path'][$i])){ 
                // B path is shorther then A, and values before were equal
                return 1;
            }
            if ($id != $b['path'][$i]){
                // compare category positions at the same level
                $p = isset($a['order'][$i]) ? $a['order'][$i] : 0;
                $p2 = isset($b['order'][$i]) ? $b['order'][$i] : 0;
                return ($p < $p2) ? -1 : 1;
            }
        }
        // B path is longer or equal then A, and values before were equal
        return ($a['value'] == $b['value']) ? 0 : -1;
    }           
       
    
}
