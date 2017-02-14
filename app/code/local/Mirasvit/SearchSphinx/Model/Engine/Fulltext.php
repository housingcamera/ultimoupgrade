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
 * Class realize methods to search in prepared search_index MySQL tables.
 *
 * @category Mirasvit
 */
class Mirasvit_SearchSphinx_Model_Engine_Fulltext extends Mirasvit_SearchIndex_Model_Engine
{
    /**
     * Prepare and excute query, return prepared results.
     *
     * @param string $queryText search term (original)
     * @param int    $store     current store ID
     * @param object $index     search index
     *
     * @return array results array [id]=>[relevance]
     */
    public function query($query, $store, $index)
    {
        $connection = $this->_getReadAdapter();
        $table = $index->getIndexer()->getTableName();
        $attributes = $this->_getAttributes($index);
        $pk = $index->getIndexer()->getPrimaryKey();

        $select = $connection->select();
        $select->from(array('s' => $table), array($pk));

        $arQuery = Mage::helper('searchsphinx/query')->buildQuery($query, $store);

        if (count($arQuery) == 0 || count($attributes) == 0) {
            return array();
        }

        $searchableAttributes = $index->getSearchableAttributes();

        $caseCondition = $this->_getCaseCondition($query, $arQuery, $attributes);
        $whereCondition = $this->_getWhereCondition($arQuery, $searchableAttributes);

        if (intval($store) > 0) {
            $select->where('s.store_id = ?', (int) $store);
        }

        if ($whereCondition != '') {
            $select->where($whereCondition);
        }

        $select->columns(array('relevance' => new Zend_Db_Expr($caseCondition)));
        $select->columns('searchindex_weight');

        $select->limit(Mage::getSingleton('searchsphinx/config')->getResultLimit());
        $select->order('relevance desc');

        $result = array();
        $weight = array();

        // echo $select.'<hr>';

        $stmt = $connection->query($select);
        while ($row = $stmt->fetch(Zend_Db::FETCH_NUM)) {
            $result[$row[0]] = $row[1];
            $weight[$row[0]] = $row[2];
        }

        $result = $this->_normalize($result);

        foreach ($result as $key => $value) {
            $result[$key] += $weight[$key];
        }
        if (isset($_GET['debug'])) {
            if ((isset($_GET['index']) && $_GET['index'] === $index->getCode()) || !isset($_GET['index'])) {
                Mage::helper('searchsphinx/debug')->searchDebug($result, $weight, $select);
            }
        }

        $result = $this->_filterByMinRelevance($result);

        return $result;
    }

    /**
     * Build sql CASE WHEN .. THEN .. ELSE .. END for SELECT section
     * (Build all query parts to calculate relevance according to attribute`s weights).
     *
     * @param string $query      search term (original)
     * @param array  $arQuery    prepared query
     * @param array  $attributes array [attributes]=>[weights]
     *
     * @return string
     */
    protected function _getCaseCondition($query, $arQuery, $attributes)
    {
        $uid = Mage::helper('mstcore/debug')->start();
        $select = '';
        $cases = array();
        $fullCases = array();
        $words = Mage::helper('core/string')->splitWords($query, true);

        foreach ($attributes as $attr => $weight) {
            if ($weight == 0) {
                continue;
            }

            $cases[$weight * 4][] = $this->getCILike('s.'.$attr, $query);
            $cases[$weight * 3][] = $this->getCILike('s.'.$attr, ' '.$query.' ', array('position' => 'any'));
        }

        foreach ($words as $word) {
            foreach ($attributes as $attr => $weight) {
                $w = intval($weight / count($arQuery));
                if ($w == 0) {
                    continue;
                }
                $cases[$w][] = $this->getCILike('s.'.$attr, $word, array('position' => 'any'));
                $cases[$w + 1][] = $this->getCILike('s.'.$attr, ' '.$word.' ', array('position' => 'any'));
            }
        }

        foreach ($words as $word) {
            foreach ($attributes as $attr => $weight) {
                $w = intval($weight / count($arQuery));

                if ($w == 0) {
                    continue;
                }

                // $locate = new Zend_Db_Expr('LOCATE("'.$word.'", s.'.$attr.')');
                // $cases[$w.'-'.$locate->__toString()][] = $locate;
                $locate = new Zend_Db_Expr('(LENGTH(s.'.$attr.') - LOCATE("'.addslashes($word).'", s.'.$attr.')) / LENGTH(s.'.$attr.')');
                $cases[$w.'*'.$locate->__toString()][] = $locate;
            }
        }

        foreach ($cases as $weight => $conds) {
            foreach ($conds as $cond) {
                $fullCases[] = 'CASE WHEN '.$cond.' THEN '.$weight.' ELSE 0 END';
            }
        }

        if (count($fullCases)) {
            $select = '('.implode('+', $fullCases).')';
        } else {
            $select = new Zend_Db_Expr('0');
        }

        Mage::helper('mstcore/debug')->end($uid, (string) $select);

        return $select;
    }

    /**
     * Return sql WHERE condition
     * WHERE consists of sections: 1 word - 1 section.
     *
     * @param array $arWords    prepared query
     * @param array $attributes array [attributes]=>[weights]
     *
     * @return string
     */
    protected function _getWhereCondition($arWords, $searchableAttributes)
    {
        if (!is_array($arWords)) {
            return '';
        }

        $result = array();
        foreach ($arWords as $key => $array) {
            $result[] = $this->_buildWhere($key, $array, $searchableAttributes);
        }

        $where = '('.implode(' AND ', $result).')';

        return $where;
    }

    /**
     * Build section for word/words.
     *
     * @param string $type  section type (AND/OR)
     * @param array  $array words
     *
     * @return array
     */
    protected function _buildWhere($type, $array, $searchableAttributes)
    {
        if (!is_array($array)) {
            $likes = array();
            foreach ($searchableAttributes as $attribute) {
                $likes[] = $this->getCILike('s.'.$attribute, $array, array('position' => 'any'), $type);
            }

            return '('.implode(' OR ', $likes).')';
        }

        foreach ($array as $key => $subarray) {
            if ($key == 'or') {
                $array[$key] = $this->_buildWhere($type, $subarray, $searchableAttributes);
                if (is_array($array[$key])) {
                    $array = '('.implode(' OR ', $array[$key]).')';
                }
            } elseif ($key == 'and') {
                $array[$key] = $this->_buildWhere($type, $subarray, $searchableAttributes);
                if (is_array($array[$key])) {
                    $array = '('.implode(' AND ', $array[$key]).')';
                }
            } else {
                $array[$key] = $this->_buildWhere($type, $subarray, $searchableAttributes);
            }
        }

        return $array;
    }

    /**
     * Return merged array of attributes and current index table fields.
     *
     * @param object $index index object
     *
     * @return array
     */
    protected function _getAttributes($index)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $attributes = $index->getAttributes(true);
        $columns = $this->_getTableColumns($index->getIndexer()->getTableName());

        foreach ($attributes as $attr => $weight) {
            if (!in_array($attr, $columns)) {
                unset($attributes[$attr]);
            }
        }

        foreach ($columns as $column) {
            if (!in_array($column, array($index->getIndexer()->getPrimaryKey(), 'store_id', 'updated'))
                && !isset($attributes[$column])) {
                $attributes[$column] = 0;
            }
        }

        Mage::helper('mstcore/debug')->end($uid, array('$attributes' => $attributes, '$index' => $index));

        return $attributes;
    }

    /**
     * Method exists only in Magento 1.6+, duplicate.
     */
    public function getCILike($field, $value, $options = array(), $type = 'LIKE')
    {
        $quotedField = $this->_getReadAdapter()->quoteIdentifier($field);

        return new Zend_Db_Expr($quotedField.' '.$type.' "'.$this->escapeLikeValue($value, $options).'"');
    }

    /**
     * Method exists only in Magento 1.6+, duplicate.
     */
    public function escapeLikeValue($value, $options = array())
    {
        $value = addslashes($value);

        $from = array();
        $to = array();
        if (empty($options['allow_string_mask'])) {
            $from[] = '%';
            $to[] = '\%';
        }
        if ($from) {
            $value = str_replace($from, $to, $value);
        }

        if (isset($options['position'])) {
            switch ($options['position']) {
                case 'any':
                    $value = '%'.$value.'%';
                    break;
                case 'start':
                    $value = $value.'%';
                    break;
                case 'end':
                    $value = '%'.$value;
                    break;
            }
        }

        return $value;
    }

    /**
     * Return columns array for DB table (tmp table *.e).
     *
     * @param string $tableName
     *
     * @return array
     */
    protected function _getTableColumns($tableName)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $columns = array_keys($this->_getReadAdapter()->describeTable($tableName));

        Mage::helper('mstcore/debug')->end($uid, array('$tableName' => $tableName, '$columns' => $columns));

        return $columns;
    }
}
