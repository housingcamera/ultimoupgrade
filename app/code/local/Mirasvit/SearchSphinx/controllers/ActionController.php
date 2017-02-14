<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.4
 * @build     1364
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



/**
 * Sphinx engine management / actoin controller (to perform all actions via apache user).
 *
 * @category Mirasvit
 */
class Mirasvit_SearchSphinx_ActionController extends Mage_Core_Controller_Front_Action
{
    public function startAction()
    {
        try {
            $this->_getEngine()->doStart();
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
    }

    public function stopAction()
    {
        try {
            $this->_getEngine()->doStop();
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
    }

    public function reindexAction()
    {
        $result = null;
        $isDelta = false;

        if ($this->getRequest()->has('delta')) {
            $isDelta = true;
        }

        try {
            $result = $this->_getEngine()->doReindex($isDelta);
        } catch (Exception $e) {
            $result = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('searchsphinx')->__($result));
    }

    protected function _getEngine()
    {
        return Mage::getSingleton('searchsphinx/engine_sphinx_native');
    }
}
