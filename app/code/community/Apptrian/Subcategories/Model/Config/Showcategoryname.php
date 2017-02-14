<?php
/**
 * @category   Apptrian
 * @package    Apptrian_Subcategories
 * @author     Apptrian
 * @copyright  Copyright (c) 2015 Apptrian (http://www.apptrian.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apptrian_Subcategories_Model_Config_Showcategoryname
{
    
    protected $_options;
	const SHOW_CATEGORY_NAME_NONE   = 'none';
    const SHOW_CATEGORY_NAME_BOTTOM = 'bottom';
    const SHOW_CATEGORY_NAME_TOP    = 'top';
    
    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options[] = array(
			   'value'=>self::SHOW_CATEGORY_NAME_NONE,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Do not show')
			);
			$this->_options[] = array(
			   'value'=>self::SHOW_CATEGORY_NAME_BOTTOM,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Bottom')
			);
            $this->_options[] = array(
			   'value'=>self::SHOW_CATEGORY_NAME_TOP,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Top')
			);
		}
		return $this->_options;
	}
    
}