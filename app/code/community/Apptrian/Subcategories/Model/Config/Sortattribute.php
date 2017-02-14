<?php
/**
 * @category   Apptrian
 * @package    Apptrian_Subcategories
 * @author     Apptrian
 * @copyright  Copyright (c) 2015 Apptrian (http://www.apptrian.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apptrian_Subcategories_Model_Config_Sortattribute
{
    
    protected $_options;
	const SORT_ATTRIBUTE_NAME       = 'name';
    const SORT_ATTRIBUTE_POSITION   = 'position';
    const SORT_ATTRIBUTE_CREATED_AT = 'created_at';
    
    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options[] = array(
			   'value'=>self::SORT_ATTRIBUTE_NAME,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Name')
			);
			$this->_options[] = array(
			   'value'=>self::SORT_ATTRIBUTE_POSITION,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Position')
			);
            $this->_options[] = array(
			   'value'=>self::SORT_ATTRIBUTE_CREATED_AT,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Created Date')
			);
		}
		return $this->_options;
	}
    
}