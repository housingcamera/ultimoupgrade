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


class Mirasvit_MstCore_Helper_Validator_Abstract extends Mage_Core_Helper_Abstract
{
    public function runTests()
    {
        $results = array();

        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (substr($method, 0, 4) == 'test') {
                $results[] = call_user_func(array($this, $method));
            }
        }

        return $results;
    }

    public function validateRewrite($class, $classNameB)
    {
        $classNameA = get_class(Mage::getModel($class));
        if ($classNameA == $classNameB) {
            return true;
        } else {
            return "$class must be $classNameB, current rewrite is $classNameA";
        }
    }

    public function dbTableExists($tableName)
    {
        return Mage::getSingleton('core/resource')
            ->getConnection('core_write')
            ->isTableExists($tableName);
    }

    public function dbDescribeTable($tableName)
    {
        return Mage::getSingleton('core/resource')
            ->getConnection('core_write')
            ->describeTable($tableName);
    }

    public function dbTableColumnExists($tableName, $column)
    {
        $desribe = $this->dbDescribeTable($tableName);

        return array_key_exists($column, $desribe);
    }
}