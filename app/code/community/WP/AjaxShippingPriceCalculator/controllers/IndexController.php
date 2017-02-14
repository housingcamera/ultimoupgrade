<?php

require_once 'Mage/Catalog/controllers/ProductController.php';

class WP_AjaxShippingPriceCalculator_IndexController extends Mage_Catalog_ProductController
{
    public function indexAction()
    {
        $product = $this->_initProduct();
        if ($product->isConfigurable()) {
            $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(
                $this->getRequest()->getParam('super_attribute'),
                $product
            );
            if (!is_null($childProduct)) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($childProduct->getId());
            }
        }
        $this->loadLayout(false);
        $block = $this->getLayout()->getBlock('shipping.calculator.result');
        if ($block) {
            $calculator = $block->getCalculator();
            $post = (array)$this->getRequest()->getPost();
            $product->setAddToCartInfo($post);
            $calculator->setProduct($product);
            $addressInfo = $this->getRequest()->getPost('calculator');
            $calculator->setAddressInfo((array)$addressInfo);
            $block->getSession()->setFormValues($addressInfo);
            try {
                $calculator->calculate();
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('catalog/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('catalog/session')->addError(
                    Mage::helper('wp_ajaxshippingpricecalculator')->__('There was an error during processing your shipping request')
                );
            }
        }
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }
}
