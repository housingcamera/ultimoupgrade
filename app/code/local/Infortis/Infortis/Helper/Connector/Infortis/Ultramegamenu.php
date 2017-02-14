<?php
/**
 * Connector for UltraMegamenu module
 */

class Infortis_Infortis_Helper_Connector_Infortis_UltraMegamenu extends Mage_Core_Helper_Abstract
{
    /**
     * Module names
     */
    const MODULE_NAME = 'Infortis_UltraMegamenu';
    const MODULE_SHORT_NAME  = 'ultramegamenu';

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
	 * Get mobile menu threshold from the menu module.
	 * If module not enabled, return NULL.
	 *
	 * @return string|NULL
	 */
	public function getMobileMenuThreshold()
	{
		if($this->isModEnabled)
		{
			return Mage::helper(self::MODULE_SHORT_NAME)->getMobileMenuThreshold();
		}
		return NULL;
	}
}
