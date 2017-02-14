<?php
/**
 * @category   Apptrian
 * @package    Apptrian_Subcategories
 * @author     Apptrian
 * @copyright  Copyright (c) 2015 Apptrian (http://www.apptrian.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apptrian_Subcategories_Model_Config_Mode
{
    
    protected $_options;
	const MODE_RANDOM   = 'random';
    const MODE_SPECIFIC = 'specific';
    
    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options[] = array(
			   'value'=>self::MODE_RANDOM,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Random')
			);
			$this->_options[] = array(
			   'value'=>self::MODE_SPECIFIC,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Specific')
			);		
		}
		return $this->_options;
	}
    
}