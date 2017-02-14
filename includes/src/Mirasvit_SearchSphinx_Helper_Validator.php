<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.2
 * @revision  754
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_SearchSphinx_Helper_Validator extends Mirasvit_MstCore_Helper_Validator_Abstract
{
    public function testTopLevelCategoryIsAnchor()
    {
        $result = self::SUCCESS;
        $title = 'Root Store Category is Anchor';
        $description = '';

        foreach (Mage::app()->getStores() as $store) {
            $rootCategoryId = $store->getRootCategoryId();
            $rootCategory = Mage::getModel('catalog/category')->load($rootCategoryId);
            if ($rootCategory->getIsAnchor() == 0) {
                $result = self::FAILED;
                $description = 'Go to the Catalog > Manage Categories and change option "Is Anchor" to "Yes"';
            }
        }

        return array($result, $title, $description);
    }

    public function testCatalogsearchLayerClass()
    {
        $result = self::SUCCESS;
        $title = 'CatalogSearch Layer Rewrite';
        $description = '';

        $validateRewrite = $this->validateRewrite('catalogsearch/layer', 'Mirasvit_SearchIndex_Model_Catalogsearch_Layer');
        if ($validateRewrite !== true) {
            $result = self::FAILED;
            $description = $validateRewrite;
        }

        return array($result, $title, $description);
    }

    public function testProductIndexExists()
    {
        $result = self::SUCCESS;
        $title = 'Search indexes are exists';
        $description = '';

        $count = Mage::getModel('searchindex/index')->getCollection()->count();
        if ($count == 0) {
            $result = self::FAILED;
            $description = 'Create required search indexes at Search / Manage Indexes';
        }

        return array($result, $title, $description);
    }

    public function testTablesExists()
    {
        $result = self::SUCCESS;
        $title = 'Required tables are exists';
        $description = [];

        $tables = array(
            'catalogsearch/fulltext',
            'searchindex/index',
            'searchsphinx/synonym',
            'searchsphinx/stopword',
        );

        foreach ($tables as $table) {
            if (!$this->dbTableExists($table)) {
                $description[] = "Table '$table' not exists";
                $result = self::FAILED;
            } 
        }

        return array($result, $title, $description);
    }

    public function testReindexIsCompleted()
    {
        $result = self::SUCCESS;
        $title = 'Search index is valid';
        $description = '';

        if (!$this->dbTableColumnExists('catalogsearch/fulltext', 'searchindex_weight')) {
            $result = self::FAILED;
            $description = 'Please run full search reindex at System / Index Management';
        }

        return array($result, $title, $description);
    }
}