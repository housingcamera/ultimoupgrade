<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Model_Observer
{
    public function applyLabels($observer)
    {
        if (!Mage::app()->getRequest()->getParam('amlabels')
        || !Mage::getSingleton('admin/session')->isAllowed('catalog/products/assign_labels'))
            return $this;

        $product = $observer->getEvent()->getProduct();

        $collection = Mage::getModel('amlabel/label')->getCollection()
            ->addFieldToFilter('include_type', array('neq' => 1));

        foreach ($collection as $label) {
            $skus = trim($label->getIncludeSku(), ', ');
            if ($skus) {
                $skus = explode(',', $skus);
            } else {
                $skus = array();
            }

            $name = 'amlabel_' . $label->getId();
            if (Mage::app()->getRequest()->getParam($name)) { // add
                if (!in_array($product->getSku(), $skus)) {
                    $skus[] = $product->getSku();
                }
            } else { // remove
                $key = array_search($product->getSku(), $skus);
                while (false !== $key) {
                    unset($skus[$key]);
                    $key = array_search($product->getSku(), $skus);
                }
            }
            $label->setIncludeSku(implode(',', $skus));
            $label->save();
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function onCoreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('catalog/products/assign_labels')) {
            $block = $observer->getBlock();
            $catalogProductEditTabsClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_edit_tabs');
            if ($catalogProductEditTabsClass == get_class($block) && $block->getProduct()->getTypeId()) {
                $name = Mage::helper('amlabel')->__('Product Labels');
                $block->addTab('general', array(
                        'label'   => $name,
                        'content' => $block->getLayout()->createBlock('amlabel/adminhtml_catalog_product_edit_labels')
                                ->setTitle($name)->toHtml(),
                    )
                );
            }
        }

        return $this;
    }

    public function onCoreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('amlabel/options/use_js', Mage::app()->getStore()->getId())) {
            return $this;
        }
        $block = $observer->getBlock();
        if ($block instanceof Mage_Catalog_Block_Product_Price) {
            $id = $block->getProduct()->getId();
            if (!Mage::registry('amlabel_product_id_' . $id)) {
                // add product ID info in output
                Mage::register('amlabel_product_id_' . $id, true, true);
                $html = $html = $observer->getTransport()->getHtml();
                $observer->getTransport()->setHtml('<div class="price" id="amlabel-product-price-' . $id . '" style="display:none"></div>' . $html);
                // add label for product
                $product = $block->getProduct();
                /*
                 * old method was:
                 *  $type    = strpos($block->getModuleName(), 'Catalog') !== false ? 'category' : 'product';
                 *
                 * new method:
                 */
                $controller = Mage::app()->getRequest()->getControllerName();
                $action     = Mage::app()->getRequest()->getActionName();
                $type       = ($controller == 'product' && $action == 'view') ? 'product' : 'category';

                $label   = Mage::helper('amlabel')->getLabels($product, $type, true);
                if ($label) {
                    $this->addScript($id, addslashes($label));
                }
            }
        }

        return $this;
    }

    private function addScript($id, $label)
    {
        $scripts = Mage::registry('amlabel_scripts');
        if (strpos($scripts, "label_product_ids[$id]") === false) {
            $scripts .= "\r\n amlabel_product_ids[$id] = '" . addslashes($label) . "'; \r\n";
        }
        Mage::unregister('amlabel_scripts');
        Mage::register('amlabel_scripts', $scripts);
    }

    public function addLabelProductCollectionScript(Varien_Event_Observer $observer)
    {
        if (Mage::registry('amlabel_getting_product')
            || !Mage::getStoreConfig('amlabel/options/use_js', Mage::app()->getStore()->getId())) {
            return $this;
        }

        /*
         * register global flag to prevent grouped/configurable/bundle products
         * loading all child products caught by observer
         */
        Mage::register('amlabel_getting_product', true, true);
        $productCollection = $observer->getCollection();
        $blockClass = get_class($productCollection);
        $blockedClasses = array(
            'Mage_Reports_Model_Resource_Product_Index_Viewed_Collection',
        );
        if (in_array($blockClass, $blockedClasses)) {
            return $this;
        }
        if ($productCollection) {
            foreach ($productCollection as $item) {
                $label = Mage::helper('amlabel')->getLabels($item, 'category', true);
                if ($label) {
                    $this->addScript($item->getId(), $label);
                }
            }
        }

        Mage::unregister('amlabel_getting_product');

        return $this;
    }

    public function addLabelProductLoadScript(Varien_Event_Observer $observer)
    {
        if (Mage::registry('amlabel_getting_product')
            || !Mage::getStoreConfig('amlabel/options/use_js', Mage::app()->getStore()->getId())) {
            return $this;
        }

        /*
         * register global flag to prevent grouped/configurable/bundle products
         * loading all child products caught by observer
         */
        $label      = '';
        $controller = Mage::app()->getRequest()->getControllerName();
        Mage::register('amlabel_getting_product', true, true);
        $product = $observer->getProduct();
        if ($product) {
            if (strpos($controller, 'cart') === false) {
                $label = Mage::helper('amlabel')->getLabels($product, 'product', true);
            }
            if ($label) {
                $this->addScript($product->getId(), $label);
            }
        }

        Mage::unregister('amlabel_getting_product');

        return $this;
    }
}