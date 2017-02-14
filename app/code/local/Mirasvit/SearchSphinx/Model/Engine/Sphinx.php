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



if (!@class_exists('SphinxClient')) {
    include Mage::getBaseDir().DS.'lib'.DS.'Sphinx'.DS.'sphinxapi.php';
}

/**
 * This class implmenets methods for:
 *     sends requests for searching
 *     configuration files assembly
 * Base class for searching in mode Search Sphinx (on another server).
 *
 * @category Mirasvit
 */
class Mirasvit_SearchSphinx_Model_Engine_Sphinx extends Mirasvit_SearchIndex_Model_Engine
{
    const PAGE_SIZE = 1000;

    protected $_config = null;
    protected $_sphinxFilepath = null;
    protected $_configFilepath = null;

    protected $_spxHost = null;
    protected $_spxPort = null;

    protected $_io = null;

    public function __construct()
    {
        $this->_config = Mage::getSingleton('searchsphinx/config');
        $this->_sphinxFilepath = Mage::getBaseDir('var').DS.'sphinx';
        $this->_configFilepath = $this->_sphinxFilepath.DS.'sphinx.conf';

        $this->_spxHost = Mage::getStoreConfig('searchsphinx/general/external_host');
        $this->_spxPort = (int) Mage::getStoreConfig('searchsphinx/general/external_port');
        $this->_basePath = Mage::getStoreConfig('searchsphinx/general/external_path');

        $this->_io = Mage::helper('searchsphinx/io');

        return $this;
    }

    /**
     * Wrap for method $this->_query.
     *
     * @param string $queryText search query (in its original form)
     * @param int    $store     curent store ID
     * @param object $index     current search index
     *
     * @return array - array of found products IDs: array(product_id => product_relevance)
     */
    public function query($queryText, $store, $index)
    {
        return $this->_query($queryText, $store, $index);
    }

    /**
     * Sends prepared request to Sphinx and forms the response.
     *
     * @param string $query   search query (in its original form)
     * @param int    $storeId curent store ID
     * @param object $index   current search index
     * @param int    $offset  page
     *
     * @return array - array of found products IDs: array(product_id => product_relevance)
     */
    protected function _query($query, $storeId, $index, $offset = 1)
    {
        $uid = Mage::helper('mstcore/debug')->start();

        $indexCode = $index->getCode();
        $primaryKey = $index->getPrimaryKey();
        $attributes = $index->getAttributes();

        $client = new SphinxClient();
        $client->setMaxQueryTime(5000); //5 seconds
        $client->setLimits(($offset - 1) * self::PAGE_SIZE, self::PAGE_SIZE, $this->_config->getResultLimit());
        $client->setSortMode(SPH_SORT_RELEVANCE);
        $client->setMatchMode(SPH_MATCH_EXTENDED);
        $client->setServer($this->_spxHost, $this->_spxPort);
        $client->SetFieldWeights($attributes);

        if ($storeId) {
            $client->SetFilter('store_id', array($storeId));
        }

        $sphinxQuery = $this->_buildQuery($query, $storeId);

        if (!$sphinxQuery) {
            return array();
        }

        $sphinxQuery = '@('.implode(',', $index->getSearchableAttributes()).')'.$sphinxQuery;

        $sphinxResult = $client->query($sphinxQuery, $indexCode);

        if ($sphinxResult === false) {
            Mage::throwException($client->GetLastError()."\nQuery: ".$query);
        } elseif ($sphinxResult['total'] > 0) {
            $entityIds = array();
            $entityIdsWeights = array();
            foreach ($sphinxResult['matches'] as $data) {
                $additionalWeight = isset($data['attrs']['searchindex_weight'])
                    ? $data['attrs']['searchindex_weight']
                    : 0;
                $entityIds[$data['attrs'][strtolower($primaryKey)]] = $data['weight'];
                $entityIdsWeights[$data['attrs'][strtolower($primaryKey)]] = $additionalWeight;
            }

            if ($sphinxResult['total'] > $offset * self::PAGE_SIZE
                && $offset * self::PAGE_SIZE < $this->_config->getResultLimit()) {
                $newIds = $this->_query($query, $storeId, $index, $offset + 1);
                foreach ($newIds as $key => $value) {
                    $entityIds[$key] = $value;
                }
            }
        } else {
            $entityIds = array();
            $entityIdsWeights = array();
        }

        $entityIds = $this->_normalize($entityIds);

        # add search index weight after normalize
        foreach ($entityIds as $id => $weight) {
            if (isset($entityIdsWeights[$id])) {
                $entityIds[$id] += $entityIdsWeights[$id];
            }
        }

        if (isset($_GET['debug'])) {
            Mage::helper('searchsphinx/debug')->searchDebug($entityIds, $entityIdsWeights, $sphinxQuery);
        }

        Mage::helper('mstcore/debug')->end($uid, $entityIds);

        $entityIds = $this->_filterByMinRelevance($entityIds);

        return $entityIds;
    }

    /**
     * Builds query to Sphinx
     * Request consists of sections (..) & (..) & ..
     *
     * @param string $query   user query
     * @param int    $storeId
     *
     * @return string
     */
    protected function _buildQuery($query, $storeId)
    {
        // Extended query syntax
        if (substr($query, 0, 1) == '=') {
            return substr($query, 1);
        }

        // Search by field
        if (substr($query, 0, 1) == '@') {
            return $query;
        }

        $arQuery = Mage::helper('searchsphinx/query')->buildQuery($query, $storeId, true);

        if (!is_array($arQuery)) {
            return false;
        }

        $result = array();
        foreach ($arQuery as $key => $array) {
            if ($key == 'not like') {
                $result[] = '-'.$this->_buildWhere($key, $array);
            } else {
                $result[] = $this->_buildWhere($key, $array);
            }
        }
        if (count($result)) {
            $query = '('.implode(' & ', $result).')';
        }

        return $query;
    }

    /**
     * Builds request sections.
     *
     * @param string $type  section type AND/OR
     * @param array  $array words for section
     *
     * @return string
     */
    protected function _buildWhere($type, $array)
    {
        if (!is_array($array)) {
            $query = '("*'.$this->escapeSphinxQL($array).'*")';
            // When wildcard search is enabled, Sphinx does not use the "morphology search".
            // Thus we also add a search keyword without wildcard character.
            if (Mage::getSingleton('searchsphinx/config')->getWildcardMode() !== Mirasvit_SearchSphinx_Model_Config::WILDCARD_DISABLED) {
                $query .= ' | ('.trim($this->escapeSphinxQL($array)).')';
            }

            return $query;
        }

        foreach ($array as $key => $subarray) {
            if ($key == 'or') {
                $array[$key] = $this->_buildWhere($type, $subarray);
                if (is_array($array[$key])) {
                    $array = '('.implode(' | ', $array[$key]).')';
                }
            } elseif ($key == 'and') {
                $array[$key] = $this->_buildWhere($type, $subarray);
                if (is_array($array[$key])) {
                    $array = '('.implode(' & ', $array[$key]).')';
                }
            } else {
                $array[$key] = $this->_buildWhere($type, $subarray);
            }
        }

        return $array;
    }

    protected function escapeSphinxQL($string)
    {
        $from = array('.', ' ', '\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=', "'");
        $to = array('\.', ' ', '\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=', "\'");

        return str_replace($from, $to, $string);
    }

    /**
     * Assembles and saves the config file for interacting with Sphinx (sphinx.conf)
     * Path to config file magento_root/var/sphinx/sphinx.conf
     * Config template located in the extension etc/config/sphinx.conf.
     *
     * @return string - full path to config file
     */
    public function makeConfigFile()
    {
        if (!$this->_io->directoryExists($this->_sphinxFilepath)) {
            $this->_io->mkdir($this->_sphinxFilepath);
        }

        $data = array(
            'time' => date('d.m.Y H:i:s'),
            'host' => $this->_spxHost,
            'port' => $this->_spxPort,
            'logdir' => $this->_basePath,
            'sphinxdir' => $this->_basePath,
        );

        $formater = new Varien_Filter_Template();
        $formater->setVariables($data);
        $config = $formater->filter(file_get_contents($this->getSphinxCfgTpl()));

        $indexes = Mage::helper('searchindex/index')->getIndexes();
        $sections = array();
        foreach ($indexes as $index) {
            $indexer = $index->getIndexer();
            $sections[$index->getCode()] = $this->_getSectionConfig($index->getCode(), $indexer);
        }
        $config .= implode(PHP_EOL, $sections);
        // $config  .= PHP_EOL.$this->_getSectionConfig($index->getCode(), $indexer);

        if ($this->_io->isWriteable($this->_configFilepath)) {
            $this->_io->write($this->_configFilepath, $config);
        } else {
            if ($this->_io->fileExists($this->_configFilepath)) {
                Mage::throwException(sprintf('File %s does not writeable', $this->_configFilepath));
            } else {
                Mage::throwException(sprintf('Directory %s does not writeable', $this->_sphinxFilepath));
            }
        }

        return $this->_configFilepath;
    }

    /**
     * Assembles a section to config file
     * Each index has its own section
     * Section consists of source (source of info) and index (where it is to write and how to index)
     * Section template located in the extension etc/config/section.conf.
     *
     * @param string $name    index code
     * @param object $indexer index indexer
     *
     * @return string - prepared section
     */
    protected function _getSectionConfig($name, $indexer)
    {
        $sqlHost = Mage::getConfig()->getNode('global/resources/default_setup/connection/host');
        $sqlPort = 3306;

        if (count(explode(':', $sqlHost)) == 2) {
            $arr = explode(':', $sqlHost);
            $sqlHost = $arr[0];
            $sqlPort = $arr[1];
        }

        $data = array(
            'name' => $name,
            'sql_host' => $sqlHost,
            'sql_port' => $sqlPort,
            'sql_user' => Mage::getConfig()->getNode('global/resources/default_setup/connection/username'),
            'sql_pass' => Mage::getConfig()->getNode('global/resources/default_setup/connection/password'),
            'sql_db' => Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname'),
            'sql_query_pre' => $this->_getSqlQueryPre($indexer),
            'sql_query' => $this->_getSqlQuery($indexer),
            'sql_query_delta' => $this->_getSqlQueryDelta($indexer),
            'sql_attr_uint' => $indexer->getPrimaryKey(),
            'min_word_len' => Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MIN_QUERY_LENGTH),
            'index_path' => $this->_basePath.DS.$name,
            'delta_index_path' => $this->_basePath.DS.$name.'_delta',
        );

        foreach ($data as $key => $value) {
            $data[$key] = str_replace('#', '\#', $value);
        }

        $formater = new Varien_Filter_Template();
        $formater->setVariables($data);
        $config = $formater->filter(file_get_contents($this->getSphinxSectionCfgTpl()));

        return $config;
    }

    /**
     * Returns base sql query (set status to updated = 0).
     *
     * @param object $indexer
     *
     * @return string
     */
    protected function _getSqlQueryPre($indexer)
    {
        $table = $indexer->getTableName();

        $sql = 'UPDATE '.$table.' SET updated=0';

        return $sql;
    }

    /**
     * Returns sql query, which is used by Sphinx to retrieve all the indexed data.
     *
     * @param object $indexer
     *
     * @return string
     */
    protected function _getSqlQuery($indexer)
    {
        $table = $indexer->getTableName();

        $sql = 'SELECT CONCAT('.$indexer->getPrimaryKey().', store_id) AS id, '.$table.'.* FROM '.$table;

        return $sql;
    }

    /**
     * Returns sql query for retrieving all the elements for delta-reindex (for updated elements).
     *
     * @param object $indexer
     *
     * @return string
     */
    protected function _getSqlQueryDelta($indexer)
    {
        $sql = $this->_getSqlQuery($indexer);
        $sql .= ' WHERE updated = 1';

        return $sql;
    }

    /**
     * Define path for sphinx config template.
     *
     * @return string
     */
    protected function getSphinxCfgTpl()
    {
        return Mage::getModuleDir('etc', 'Mirasvit_SearchSphinx').DS.'conf'.DS.$this->getSphinxVersion().DS.'sphinx.conf';
    }

    /**
     * Define path for sphinx section template.
     *
     * @return string
     */
    protected function getSphinxSectionCfgTpl()
    {
        return Mage::getModuleDir('etc', 'Mirasvit_SearchSphinx').DS.'conf'.DS.$this->getSphinxVersion().DS.'section.conf';
    }

    /**
     * Default sphinx version.
     *
     * @return string
     */
    protected function getSphinxVersion()
    {
        return '2.0';
    }
}
