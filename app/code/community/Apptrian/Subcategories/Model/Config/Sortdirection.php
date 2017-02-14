<?php
/**
 * @category   Apptrian
 * @package    Apptrian_Subcategories
 * @author     Apptrian
 * @copyright  Copyright (c) 2015 Apptrian (http://www.apptrian.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apptrian_Subcategories_Model_Config_Sortdirection
{
    
    protected $_options;
	const SORT_DIRECTION_ASC  = 'asc';
    const SORT_DIRECTION_DESC = 'desc';
    
    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options[] = array(
			   'value'=>self::SORT_DIRECTION_ASC,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Ascending')
			);
			$this->_options[] = array(
			   'value'=>self::SORT_DIRECTION_DESC,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Descending')
			);
		}
		return $this->_options;
	}
    
}