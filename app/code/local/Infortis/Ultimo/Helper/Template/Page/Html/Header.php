<?php

class Infortis_Ultimo_Helper_Template_Page_Html_Header extends Mage_Core_Helper_Abstract
{
    /**
     *@deprecated since 1.16.0
     * Menu module name
     *
     * @var string
     */
    protected $_menuModuleName = 'Infortis_UltraMegamenu';
    protected $_menuModuleNameShort = 'ultramegamenu';

    /**
     * Main helper of the theme
     *
     * @var Infortis_Ultimo_Helper_Data
     */
    protected $theme;

    /**
     * Positions of header blocks
     *
     * @var array
     */
    protected $position;

    /**
     * Initialization
     */
    public function __construct()
    {
        $this->theme = Mage::helper('ultimo');

        $this->position['logo']             = $this->theme->getCfg('header/logo_position');
        $this->position['main-menu']        = $this->theme->getCfg('header/main_menu_position');
        $this->position['search']           = $this->theme->getCfg('header/search_position');
        $this->position['account-links']    = $this->theme->getCfg('header/account_links_position');
        $this->position['user-menu']        = $this->theme->getCfg('header/user_menu_position');
        $this->position['compare']          = $this->theme->getCfg('header/compare_position');
        $this->position['cart']             = $this->theme->getCfg('header/cart_position');
        $this->position['currency']         = $this->theme->getCfg('header/currency_switcher_position');
        $this->position['language']         = $this->theme->getCfg('header/lang_switcher_position');
    }

    /**
     * Get positions of header blocks
     *
     * @return array
     */
    public function getPositions()
    {
        return $this->position;
    }

    /**
     * Create grid classes for header sections
     *
     * @return array
     */
    public function getGridClasses()
    {
        //Width (in grid units) of product page sections
        $primLeftColUnits       = $this->theme->getCfg('header/left_column');
        $primCentralColUnits    = $this->theme->getCfg('header/central_column');
        $primRightColUnits      = $this->theme->getCfg('header/right_column');

        //Grid classes
        $grid = array();
        $classPrefix = 'grid12-';

        if (!empty($primLeftColUnits) && trim($primLeftColUnits) !== '')
        {
            $grid['primLeftCol']        = $classPrefix . $primLeftColUnits;
        }

        if (!empty($primCentralColUnits) && trim($primCentralColUnits) !== '')
        {
            $grid['primCentralCol']     = $classPrefix . $primCentralColUnits;
        }

        if (!empty($primRightColUnits) && trim($primRightColUnits) !== '')
        {
            $grid['primRightCol']       = $classPrefix . $primRightColUnits;
        }

        return $grid;
    }

    /**
     * Check if main menu is displayed inisde a section (full-width section) at the bottom of the header
     *
     * @return bool
     */
    public function isMenuDisplayedInFullWidthContainer()
    {
        if ($this->position['main-menu'] === 'menuContainer')
        {
            return true;
        }
        return false;
    }

    /**
     * @deprecated since 1.16.0
     * Get array of flags indicating if blocks are displayed directly inside the header block template (true)
     * or inside one of the child blocks (false).
     *
     * @return array
     */
    public function getDisplayedInHeaderBlock()
    {
        //List of blocks that are displayed directly inside the header block template.
        //Important: it can contain only the blocks which can be optionally added to the User Menu.
        $display = array();
        $display['search']  = true;
        $display['cart']    = true;
        $display['compare'] = true;

        if ($this->position['search'] === 'userMenu' || $this->position['search'] === 'mainMenu')
        {
            $display['search'] = false;
        }

        if ($this->position['cart'] === 'userMenu' || $this->position['cart'] === 'mainMenu')
        {
            $display['cart'] = false;
        }

        if ($this->position['compare'] === 'userMenu' || $this->position['compare'] === 'mainMenu')
        {
            $display['compare'] = false;
        }

        return $display;
    }

    /**
     * @deprecated since 1.16.0. Replaced with connector.
     * Get mobile menu threshold from the menu module.
     * If module not enabled, return NULL.
     *
     * @return string
     */
    public function getMobileMenuThreshold()
    {
        if(Mage::helper('core')->isModuleEnabled($this->_menuModuleName))
        {
            return Mage::helper($this->_menuModuleNameShort)->getMobileMenuThreshold();
        }
        return NULL;
    }

    /**
     * Get array of flags indicating if child blocks of the header (e.g. cart) are displayed inside main menu.
     * Important: can be used in modules connectors.
     *
     * @return array
     */
    public function getIsDisplayedInMenu()
    {
        $display['search']  = false;
        $display['cart']    = false;
        $display['compare'] = false;

        if ($this->position['search'] === 'mainMenu')
        {
            $display['search'] = true;
        }

        if ($this->position['cart'] === 'mainMenu')
        {
            $display['cart'] = true;
        }

        if ($this->position['compare'] === 'mainMenu')
        {
            $display['compare'] = true;
        }

        return $display;
    }

    /**
     * Get array of flags indicating if child blocks of the header (e.g. cart) are displayed inside user menu.
     *
     * @return array
     */
    public function getIsDisplayedInUserMenu()
    {
        $display['search']          = false;
        $display['cart']            = false;
        $display['compare']         = false;
        $display['account-links']   = false;

        if ($this->position['search'] === 'userMenu')
        {
            $display['search'] = true;
        }

        if ($this->position['cart'] === 'userMenu')
        {
            $display['cart'] = true;
        }

        if ($this->position['compare'] === 'userMenu')
        {
            $display['compare'] = true;
        }

        if ($this->position['account-links'] === 'userMenu')
        {
            $display['account-links'] = true;
        }

        return $display;
    }

}
