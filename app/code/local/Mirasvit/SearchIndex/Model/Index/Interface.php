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



interface Mirasvit_SearchIndex_Model_Index_Interface
{
    public function getBaseGroup();
    public function getBaseTitle();

    public function getPK();

    public function getFieldsets();
    public function getAvailableAttributes();

    public function canUse();
    public function isLocked();

    public function getEntityModel();
}
