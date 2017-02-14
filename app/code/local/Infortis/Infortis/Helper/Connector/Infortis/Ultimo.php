<?php
/**
 * Connector for Ultimo module
 */

class Infortis_Infortis_Helper_Connector_Infortis_Ultimo extends Mage_Core_Helper_Abstract
{
    /**
     * Module names
     */
    const MODULE_NAME = 'Infortis_Ultimo';
    //const MODULE_SHORT_NAME  = 'ultimo';
    const HELPER_TEMPLATE_PAGE_HTML_HEADER = 'ultimo/template_page_html_header';

	/**
	 * Module enabled flag
	 *
	 * @var bool
	 */
	protected $isModEnabled;

	/**
	 * Initialization
	 */
	public function __construct()
	{
		$this->isModEnabled = Mage::helper('core')->isModuleEnabled(self::MODULE_NAME);
	}

	/**
	 * Get array of flags indicating if child blocks of the header (e.g. cart) are displayed inside main menu
	 * If module not enabled, return NULL.
	 *
	 * @return array|NULL
	 */
	public function getIsDisplayedInMenu()
	{
		if($this->isModEnabled)
		{
			return Mage::helper(self::HELPER_TEMPLATE_PAGE_HTML_HEADER)->getIsDisplayedInMenu();
		}
		return NULL;
	}
}
