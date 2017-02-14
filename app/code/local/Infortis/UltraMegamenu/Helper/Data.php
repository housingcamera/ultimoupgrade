<?php

class Infortis_UltraMegamenu_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Section name of module configuration
     */
    const CONFIG_SECTION = 'ultramegamenu';

    /**
     * Get configuration
     *
     * @var string
     */
    public function getCfg($optionString)
    {
        return Mage::getStoreConfig(self::CONFIG_SECTION . '/' . $optionString);
    }

    /**
     * Get mobile menu threshold if mobile mode enabled. Otherwise, return NULL.
     * Important: can be used in modules connectors.
     *
     * @var string/NULL
     */
    public function getMobileMenuThreshold()
    {
        if ($this->getCfg('general/mode') > 0) //Mobile mode not enabled
        {
            return NULL; //If no mobile menu, value of the threshold doesn't matter, so return NULL
        }
        else
        {
            return $this->getCfg('mobilemenu/threshold');
        }
    }

    /**
     * Get CSS class
     *
     * @return string
     */
    public function getBlocksVisibilityClassOnMobile()
    {
        // Special class to show items with category blocks but without subcategories
        $showItemsOnlyBlocksClass = ($this->getCfg('mobilemenu/show_items_only_blocks')) ? ' opt-sob' : '';

        // Class indicating to hide category blocks below predefined breakpoint
        $hideBlocksBelowClass = ($this->getCfg('mobilemenu/hide_blocks_below')) ? ' opt-hide480' : '';

        // Class that shows/hides category blocks of selected levels
        return 'opt-sb' . $this->getCfg('mobilemenu/show_blocks') . $showItemsOnlyBlocksClass . $hideBlocksBelowClass;
    }

    /**
     * Check if current url is url for home page
     *
     * @return bool
     */
    public function getIsHomePage()
    {
        return Mage::getUrl('') == Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));

        //Alternative way, requires testing. 
        // $homeUrl = $this->getUrl('')
        // $currentUrl = $this->getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
        // if (strpos($currentUrl, '?') !== false) $currentUrl = array_shift(explode('?', $currentUrl));
        // return $homeUrl == $currentUrl;
    }

    /**
     * @deprecated
     * Check if current url is url for home page
     *
     * @return bool
     */
    public function getIsOnHome()
    {
        return Mage::getUrl('') == Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
    }

    /**
     * Check if sidebar menu can be the main menu
     *
     * @var bool
     */
    public function isSidebarMenuMainMenu($sidebarIsMainMenu = NULL)
    {
        if ($sidebarIsMainMenu === NULL) //Param not set
        {
            $sidebarIsMainMenu = $this->getCfg('sidemenu/is_main');
        }

        if ($sidebarIsMainMenu)
        {
            //The sidebar menu was explicitly marked as the main menu
            return true;
        }
        else
        {
            //Check if the top menu exists
            if (Mage::registry('umm_top_menu_exists'))
            {
                return false;
            }
            else
            {
                //If the top menu doesn't exist, mark the sidebar menu as the main menu
                return true;
            }
        }
    }

    /**
     * Get container
     *
     * @return string
     */
    public function getOutermostContainer()
    {
        $result = "undefined";
        $value = $this->getCfg('mainmenu/outermost_container');

        if ($value === 'window')
        {
            $result = "'window'"; //Important: single quotes required for JavaScript code
        }
        elseif ($value === 'menuBar')
        {
            $result = "undefined";
        }
        elseif ($value === 'headPrimInner')
        {
            $result = "jQuery('.hp-blocks-holder')"; //CSS class of the inner container inside the primary header
        }

        return $result;
    }

    /**
     * Get container
     *
     * @return string
     */
    public function getFullwidthDropdownContainer()
    {
        $result = "undefined";
        $value = $this->getCfg('mainmenu/fullwidth_dd_container');

        if ($value === 'window')
        {
            $result = "'window'"; //Important: single quotes required
        }
        elseif ($value === 'menuBar')
        {
            $result = "undefined";
        }
        elseif ($value === 'headPrimInner')
        {
            $result = "jQuery('.hp-blocks-holder')";
        }

        return $result;
    }
}
