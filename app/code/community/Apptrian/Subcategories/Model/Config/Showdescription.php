<?php
/**
 * @category   Apptrian
 * @package    Apptrian_Subcategories
 * @author     Apptrian
 * @copyright  Copyright (c) 2015 Apptrian (http://www.apptrian.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apptrian_Subcategories_Model_Config_Showdescription
{
    
    protected $_options;
	const SHOW_DESCRIPTION_NONE             = 'none';
    const SHOW_DESCRIPTION_DESCRIPTION      = 'description';
    const SHOW_DESCRIPTION_META_DESCRIPTION = 'meta_description';
    
    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options[] = array(
			   'value'=>self::SHOW_DESCRIPTION_NONE ,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Do not show')
			);
			$this->_options[] = array(
			   'value'=>self::SHOW_DESCRIPTION_DESCRIPTION,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Description')
			);
            $this->_options[] = array(
			   'value'=>self::SHOW_DESCRIPTION_META_DESCRIPTION,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Meta Description')
			);
		}
		return $this->_options;
	}
    
}