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
 * @category Mirasvit
 */
class Mirasvit_SearchSphinx_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Return Search engine object (Sphinx (External), Sphinx Native or Fulltext).
     *
     * @return object
     */
    public function getEngine()
    {
        $uid = Mage::helper('mstcore/debug')->start();

        switch (Mage::getSingleton('searchsphinx/config')->getSearchEngine()) {
            case Mirasvit_SearchSphinx_Model_Config::ENGINE_SPHINX:
                $engine = Mage::getSingleton('searchsphinx/engine_sphinx_native');
            break;

            case Mirasvit_SearchSphinx_Model_Config::ENGINE_SPHINX_EXTERNAL:
                $engine = Mage::getSingleton('searchsphinx/engine_sphinx');
            break;

            default:
                $engine = Mage::getSingleton('searchsphinx/engine_fulltext');
            break;
        }

        Mage::helper('mstcore/debug')->end($uid, $engine);

        return $engine;
    }

    /**
     * Check if word is wildcard exception.
     *
     * @param string $word
     *
     * @return bool
     */
    public function isWildcardException($word)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $exceptions = Mage::getSingleton('searchsphinx/config')->getWildCardExceptions();

        $result = in_array($word, $exceptions);

        Mage::helper('mstcore/debug')->end($uid, $result);

        return $result;
    }
}
