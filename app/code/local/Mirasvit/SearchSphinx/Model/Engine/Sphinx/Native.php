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
 * Mirasvit_SearchSphinx_Model_Engine_Sphinx_Native implement methods to work with Sphinx engine on the same server as Magento
 * Extends the base cless Mirasvit_SearchSphinx_Model_Engine_Sphinx with methods to manage index and Sphinx daemon e.g. reindex/delta reindex/stop/start.
 *
 * @category Mirasvit
 */
class Mirasvit_SearchSphinx_Model_Engine_Sphinx_Native extends Mirasvit_SearchSphinx_Model_Engine_Sphinx
{
    const SEARCHD = 'searchd';
    const INDEXER = 'indexer';
    const REINDEX_SUCCESS_MESSAGE = 'rotating indices: succesfully sent SIGHUP to searchd';

    protected $_indexerCommand = null;
    protected $_searchdCommand = null;

    /**
     * set variables for local Spinx engine.
     */
    public function __construct()
    {
        parent::__construct();

        $binPath = Mage::getStoreConfig('searchsphinx/general/bin_path');
        // if path contains "searchd" - remove it
        if (substr($binPath, strlen($binPath) - strlen(self::SEARCHD)) == self::SEARCHD) {
            $binPath = substr($binPath, 0, strlen($binPath) - strlen(self::SEARCHD));
        }

        $this->_indexerCommand = $binPath.self::INDEXER;
        $this->_searchdCommand = $binPath.self::SEARCHD;

        $this->_spxHost = Mage::getStoreConfig('searchsphinx/general/host');
        $this->_spxPort = Mage::getStoreConfig('searchsphinx/general/port');

        $this->_spxHost = $this->_spxHost ? $this->_spxHost : 'localhost';
        $this->_spxPort = intval($this->_spxPort ? $this->_spxPort : '9315');

        $this->_basePath = Mage::getBaseDir('var').DS.'sphinx';

        return $this;
    }

    /**
     * Reindex - http request.
     *
     * @param bool $delta perform delta-reindex
     *
     * @return string
     */
    public function reindex($delta = false)
    {
        return $this->_request('reindex/delta/'.$delta);
    }

    /**
     * Sphinx daemon start - http request.
     */
    public function start()
    {
        $error = $this->_request('start');

        if ($error) {
            Mage::throwException($error);
        }

        return $this;
    }

    /**
     * Sphinx daemon stop - http request.
     */
    public function stop()
    {
        $error = $this->_request('stop');

        if ($error) {
            Mage::throwException($error);
        }

        return $this;
    }

    /**
     * Sphinx daemon restart - sending 2 http requests (stop, start).
     */
    public function restart()
    {
        $this->stop();
        $this->start();

        return $this;
    }

    /**
     * Perform reindex of all active indexes.
     *
     * @param bool $delta perform delta-reindex
     *
     * @return string
     */
    public function doReindex($delta = false)
    {
        $this->makeConfigFile();

        if (!$this->isSphinxFounded()) {
            Mage::throwException($this->_indexerCommand.': command not found');
        }

        if (!$this->isIndexerRunning()) {
            $indexes = Mage::helper('searchindex/index')->getIndexes();
            $toReindex = array();
            foreach ($indexes as $index) {
                $indexCode = $index->getCode();
                if ($delta) {
                    $indexCode = 'delta_'.$indexCode;
                }
                $toReindex[] = $indexCode;
            }

            $exec = $this->_exec($this->_indexerCommand.' --config '.$this->_configFilepath.' --rotate '.implode(' ', $toReindex));
            $result = ($exec['status'] == 0) || (strpos($exec['data'], self::REINDEX_SUCCESS_MESSAGE) !== false);

            if (!$result) {
                Mage::throwException('Error on reindex '.$exec['data']);
            }

            if ($delta) {
                $this->mergeDeltaWithMain();
            }
            $this->restart();
        } else {
            Mage::throwException('Reindex already run, please wait... '.$this->isIndexerRunning());
        }

        return 'Index has been successfully rebuilt';
    }

    /**
     * Run Sphinx daemon.
     */
    public function doStart()
    {
        $this->stop();

        if (!$this->isSphinxFounded()) {
            Mage::throwException($this->_searchdCommand.': command not found');
        }

        if (!is_readable($this->_configFilepath)) {
            Mage::throwException('Please run full reindex, before start sphinx daemon');
        }

        $command = $this->_searchdCommand.' --config '.$this->_configFilepath;
        $exec = $this->_exec($command);
        if ($exec['status'] !== 0) {
            Mage::throwException('Error when running searchd '.$exec['data']);
        }

        return $this;
    }

    /**
     * Stop Sphinx daemon.
     */
    public function doStop()
    {
        $find = 'ps aux | grep searchd | grep '.$this->_configFilepath.' | grep -v \'grep\' | awk \'{print $2}\'';
        $exec = $this->_exec($find);

        if ($exec['data']) {
            $command = $this->_searchdCommand.' --config '.$this->_configFilepath.' --stop';
            $exec = $this->_exec($command);
            if ($exec['status'] !== 0) {
                Mage::throwException('Error when stopping searchd '.$exec['data']);
            }
        }

        return $this;
    }

    /**
     * Check if reindex is running.
     *
     * @return bool
     */
    public function isIndexerRunning()
    {
        $status = false;

        $command = 'ps aux | grep '.self::INDEXER.' | grep '.$this->_configFilepath;
        $exec = $this->_exec($command);
        if ($exec['status'] === 0) {
            $pos = strpos($exec['data'], '--rotate');
            if ($pos !== false) {
                $status = $exec['data'];

                return $status;
            }
        }

        return $status;
    }

    /**
     * Check if Spinx daemon is running.
     *
     * @return bool
     */
    public function isSearchdRunning()
    {
        $command = 'ps aux | grep '.self::SEARCHD.' | grep '.$this->_configFilepath;
        $exec = $this->_exec($command);

        if ($exec['status'] === 0) {
            $pos = strpos($exec['data'], self::SEARCHD.' --config');

            if ($pos !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if Sphinx engine exists (searchd).
     *
     * @return bool
     */
    public function isSphinxFounded()
    {
        $exec = $this->_exec($this->_searchdCommand.' --config /fake/fake/sphinx.conf');

        if (strpos($exec['data'], 'sphinx.conf') === false) {
            return false;
        }

        return true;
    }

    /**
     * Run merge of regular index with delta-index for all active indexes.
     */
    public function mergeDeltaWithMain()
    {
        $indexes = Mage::helper('searchindex/index')->getIndexes();
        foreach ($indexes as $index) {
            $exec = $this->_exec($this->_indexerCommand.' --config '.$this->_configFilepath.' --merge '.$index->getCode().' delta_'.$index->getCode().' --merge-dst-range deleted 0 0 --rotate');
        }

        return $this;
    }

    /**
     * Run php command exec() with check if function exist
     * Some servers ignore this function. To enable you have to uncomment it in php.ini.
     *
     * @param string $command command e.g. (start/stop/reindex)
     *
     * @return array
     */
    protected function _exec($command)
    {
        $status = null;
        $data = array();

        if (function_exists('exec')) {
            exec($command, $data, $status);
            Mage::helper('mstcore/logger')->log($this, __FUNCTION__, $command."\n".implode("\n", $data));
        } else {
            Mage::helper('mstcore/logger')->log($this, __FUNCTION__, 'PHP function "exec" not available');

            Mage::throwException('PHP function "exec" not available');
        }

        return array('status' => $status, 'data' => implode(PHP_EOL, $data));
    }

    /**
     * Send http request to extension`s controller.
     * So all actions with Sphinx engine performs via apache user.
     *
     * @param string $command command e.g. (start/stop/reindex)
     *
     * @return string
     */
    protected function _request($command)
    {
        $httpClient = new Zend_Http_Client();
        $httpClient->setConfig(array('timeout' => 60000));

        Mage::register('custom_entry_point', true, true);

        $store = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStore();
        $url = $store->getUrl('searchsphinx/action/'.$command, array('_query' => array('rand' => microtime(true))));
        $result = $httpClient->setUri($url)->request()->getBody();

        Mage::helper('mstcore/logger')->log($this, __FUNCTION__, $url."\n".$result);

        return $result;
    }

    /**
     * Define sphinx version.
     *
     * @return string $version
     */
    public function getSphinxVersion()
    {
        $version = '2.0';
        $cmd = $this->_searchdCommand.' --help';
        $exec = $this->_exec($cmd);
        $res = preg_match('/Sphinx[\s]?([\d.]*)([\s\w\d.-]*)?/i', $exec['data'], $match);
        if ($res === 1 && ($match[1] != '' || null != $match[1])) {
            if ($match[1] > 2.1) {
                $version = '2.2';
            }
        }

        return $version;
    }
}
