<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_SeoSuite
 * @copyright  Copyright (c) 2010 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * SEO Suite extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoSuite
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_SeoSuite_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{

    public function getPagerUrl($params=array())
    {
        if ($identifier = Mage::app()->getRequest()->getParam('am_landing')) {
    		if (count($params) > 0) {
    			return $identifier . '?' . http_build_query($params);	
    		}
    		return $identifier; 
    	}
		
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return Mage::helper('seosuite')->getLayerFilterUrl($urlParams);
    }
    
    public function getPagerHtml()
    {
        if ($identifier = Mage::app()->getRequest()->getParam('am_landing')) {
            $alias = 'product_list_toolbar_pager';
            $oldPager   = $this->getChild($alias);

            if ($oldPager instanceof Varien_Object){
                $newPager = $this->getLayout()->createBlock('amlanding/catalog_pager')
                    ->setArea('frontend')
                    ->setTemplate($oldPager->getTemplate());
                    
                $newPager->assign('_type', 'html')
                         ->assign('_section', 'body');
                         
                $this->setChild($alias, $newPager);
            }        
            return parent::getPagerHtml();
        }
        return parent::getPagerHtml();
    }
}
