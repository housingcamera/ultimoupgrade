<?php
/**
 * @category   Apptrian
 * @package    Apptrian_Subcategories
 * @author     Apptrian
 * @copyright  Copyright (c) 2015 Apptrian (http://www.apptrian.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apptrian_Subcategories_Model_Config_Showimage
{
    
    protected $_options;
    const SHOW_IMAGE_NONE      = 'none';
    const SHOW_IMAGE_IMAGE     = 'image';
    const SHOW_IMAGE_THUMBNAIL = 'thumbnail';
    
    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options[] = array(
			   'value'=>self::SHOW_IMAGE_NONE,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Do not show')
			);
			$this->_options[] = array(
			   'value'=>self::SHOW_IMAGE_IMAGE,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Image')
			);
            $this->_options[] = array(
			   'value'=>self::SHOW_IMAGE_THUMBNAIL,
			   'label'=>Mage::helper('apptrian_subcategories')->__('Thumbnail')
			);
		}
		return $this->_options;
	}
    
}