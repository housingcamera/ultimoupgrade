<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */
class Amasty_Regions_Model_Resource_Region extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('amregions/region', 'region_id');
    }

    /**
     * @param $regionId
     *
     * @return array
     */
    public function getCountries($regionId)
    {
        $db = $this->_getWriteAdapter();
        $select = $db->select()->from($this->getTable('amregions/region_country'), 'country_id')->where('region_id = ?', $regionId);
        return $db->fetchCol($select);
    }

    /**
     * @param int $regionId
     * @param array $ids
     */
    public function setCountries($ids, $regionId)
    {
        $db = $this->_getWriteAdapter();
        $table = $this->getTable('amregions/region_country');
        $db->delete($table, 'region_id = '.$regionId);

        if(count($ids) == 0) {
            return;
        }

        $sql = array();
        foreach($ids as $id) {
            $sql[] = "({$regionId}, '{$id}')";
        }

        $sql = "INSERT INTO `{$table}` (region_id, country_id) VALUES ".implode(",",$sql);
        $db->raw_query($sql);

    }
}