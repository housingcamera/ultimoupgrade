<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2013 Amasty (http://www.amasty.com)
* @package Amasty_Cart
*/
require_once Mage::getModuleDir('controllers', 'Mage_Wishlist').DS.'IndexController.php';
class Amasty_Cart_WishlistController extends Mage_Wishlist_IndexController
{
    public function preDispatch()
    {
        Mage_Core_Controller_Front_Action::preDispatch();

        //   disable check customer session
        /*
         parent::preDispatch();
        if (!$this->_skipAuthentication && !Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            if (!Mage::getSingleton('customer/session')->getBeforeWishlistUrl()) {
                Mage::getSingleton('customer/session')->setBeforeWishlistUrl($this->_getRefererUrl());
            }
            Mage::getSingleton('customer/session')->setBeforeWishlistRequest($this->getRequest()->getParams());
        }
        if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
            $this->norouteAction();
            return;
        }*/
    }

    public function addAction()
    {
        if (Mage::app()->getRequest()->isAjax()){
            $this->_addItemToWishList();

        }
        else{
           $this->_parentAddItemToWishList();
        }

    }

    protected function _addItemToWishList()
    {
        $url = Mage::getUrl('wishlist');
        $answerData = array(
            'title'     =>  $this->__('Information'),
            'message'   =>  $this->__('An error occurred while adding product to wishlist.'),
            'b1_name'   =>  $this->__('Wishlist'),
            'b2_name'   =>  $this->__('Continue'),
            'b1_action' =>  'document.location = "' . $url . '";',
            'b2_action' =>  'jQuery.confirm.hide();',
            'align'     =>  'jQuery.confirm.hide();'
        );
        if(0 < Mage::helper('amcart')->getTime()){
            $answerData['b2_name'] .= '(' . Mage::helper('amcart')->getTime() . ')';
        }

        if( !Mage::getSingleton('customer/session')->isLoggedIn()){
            $answerData['message'] = $this->__('Please login for adding product to wishlist.');

            $url = Mage::getUrl('customer/account/login');
            $answerData['redirect'] =  'document.location = "' . $url . '";';

            $this->_sendAnswer($answerData);
            return;
        }
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            $answerData['message'] = $this->__('An error occurred while adding item to wishlist.');
            $this->_sendAnswer($answerData);
            return;
        }

        $session = Mage::getSingleton('customer/session');

        $productId = (int)$this->getRequest()->getParam('product_id');
        if (!$productId) {
            $answerData['message'] = $this->__('An error occurred while adding item to wishlist.');
            $this->_sendAnswer($answerData);
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $answerData['message'] = $this->__('Cannot specify product.');
            $this->_sendAnswer($answerData);
            return;
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            Mage::helper('wishlist')->calculate();

            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping.',
                $product->getName(), Mage::helper('core')->escapeUrl($referer));

            $answerData['message'] = $message;
            $this->_sendAnswer($answerData);
            return;
        } catch (Mage_Core_Exception $e) {
            $answerData['message'] = $this->__('An error occurred while adding item to wishlist: %s', $e->getMessage());
            $this->_sendAnswer($answerData);
            return;
        }
        catch (Exception $e) {
            $answerData['message'] = $this->__('An error occurred while adding item to wishlist.');
            $this->_sendAnswer($answerData);
            return;
        }
    }

    protected function _sendAnswer($result)
    {
        $this->getResponse()->setBody(
            Zend_Json::encode($result)
        );
    }

    protected function _parentAddItemToWishList()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }

        $session = Mage::getSingleton('customer/session');

        $productId = (int)$this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            Mage::helper('wishlist')->calculate();

            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping.',
                $product->getName(), Mage::helper('core')->escapeUrl($referer));
            $session->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist.'));
        }

        $this->_redirect('wishlist', array('wishlist_id' => $wishlist->getId()));
    }


}
