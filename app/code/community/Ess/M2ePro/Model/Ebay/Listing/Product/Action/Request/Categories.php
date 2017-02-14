<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Categories
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $categoryTemplate = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private $otherCategoryTemplate = NULL;

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->getCategoriesData();
        $data['item_specifics'] = $this->getItemSpecificsData();

        if ($this->getMotorsHelper()->isMarketplaceSupportsEpid($this->getMarketplace()->getId())) {
            $tempData = $this->getMotorsData(
                Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID
            );
            $tempData !== false && $data['motors_epids'] = $tempData;
        }

        if ($this->getMotorsHelper()->isMarketplaceSupportsKtype($this->getMarketplace()->getId())) {
            $tempData = $this->getMotorsData(
                Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE
            );
            $tempData !== false && $data['motors_ktypes'] = $tempData;
        }

        return $data;
    }

    //########################################

    /**
     * @return array
     */
    public function getCategoriesData()
    {
        $data = array(
            'category_main_id' => $this->getCategorySource()->getMainCategory(),
            'category_secondary_id' => 0,
            'store_category_main_id' => 0,
            'store_category_secondary_id' => 0
        );

        if (!is_null($this->getOtherCategoryTemplate())) {
            $data['category_secondary_id'] = $this->getOtherCategorySource()->getSecondaryCategory();
            $data['store_category_main_id'] = $this->getOtherCategorySource()->getStoreCategoryMain();
            $data['store_category_secondary_id'] = $this->getOtherCategorySource()->getStoreCategorySecondary();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getItemSpecificsData()
    {
        $data = array();

        foreach ($this->getCategoryTemplate()->getSpecifics(true) as $specific) {

            /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */

            $this->searchNotFoundAttributes();

            $tempAttributeLabel = $specific->getSource($this->getMagentoProduct())
                                           ->getLabel();
            $tempAttributeValues = $specific->getSource($this->getMagentoProduct())
                                            ->getValues();

            if (!$this->processNotFoundAttributes('Specifics')) {
                continue;
            }

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue;
            }

            $data[] = array(
                'name' => $tempAttributeLabel,
                'value' => $values
            );
        }

        return $data;
    }

    public function getMotorsData($type)
    {
        $attribute = $this->getMotorsAttribute($type);

        if (empty($attribute)) {
            return false;
        }

        $this->searchNotFoundAttributes();

        $rawData = $this->getRawMotorsData($type);

        if (!$this->processNotFoundAttributes('Compatibility')) {
            return false;
        }

        if ($type == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID) {
            return $this->getPreparedMotorsEpidsData($rawData);
        }

        if ($type == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE) {
            return $this->getPreparedMotorsKtypesData($rawData);
        }

        return NULL;
    }

    //########################################

    private function getRawMotorsData($type)
    {
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($this->getMotorsAttribute($type));

        if (empty($attributeValue)) {
            return array();
        }

        $motorsData = $this->getMotorsHelper()->parseAttributeValue($attributeValue);

        $motorsData = array_merge(
            $this->prepareRawMotorsItems($motorsData['items'], $type),
            $this->prepareRawMotorsFilters($motorsData['filters'], $type),
            $this->prepareRawMotorsGroups($motorsData['groups'], $type)
        );

        return $this->filterDuplicatedData($motorsData, $type);
    }

    private function filterDuplicatedData($motorsData, $type)
    {
        $uniqueItems = array();
        $uniqueFilters = array();
        $uniqueFiltersInfo = array();

        $itemType = $this->getMotorsHelper()->getIdentifierKey($type);

        foreach ($motorsData as $item) {

            if ($item['type'] === $itemType) {
                $uniqueItems[$item['id']] = $item;
                continue;
            }

            if (!in_array($item['info'], $uniqueFiltersInfo)) {
                $uniqueFilters[] = $item;
                $uniqueFiltersInfo[] = $item['info'];
            }
        }

        return array_merge(
            $uniqueItems,
            $uniqueFilters
        );
    }
    // ---------------------------------------

    private function prepareRawMotorsItems($data, $type)
    {
        if (empty($data)) {
            return array();
        }

        $typeIdentifier = $this->getMotorsHelper()->getIdentifierKey($type);

        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($this->getMotorsHelper()->getDictionaryTable($type))
            ->where(
                '`'.$typeIdentifier.'` IN (?)',
                array_keys($data)
            );

        foreach ($select->query()->fetchAll() as $attributeRow) {
            $data[$attributeRow[$typeIdentifier]]['info'] = $attributeRow;
            $data[$attributeRow[$typeIdentifier]]['type'] = $typeIdentifier;
        }

        return $data;
    }

    private function prepareRawMotorsFilters($data, $type)
    {
        if (empty($data)) {
            return array();
        }

        $result = array();
        $typeIdentifier = $this->getMotorsHelper()->getIdentifierKey($type);

        foreach ($data as $filterId) {

            /** @var Ess_M2ePro_Model_Ebay_Motor_Filter $filter */
            $filter = Mage::getModel('M2ePro/Ebay_Motor_Filter')->load($filterId);

            if ($filter->getType() != $type) {
                continue;
            }

            $conditions = $filter->getConditions();

            $select = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from($this->getMotorsHelper()->getDictionaryTable($type));

            foreach ($conditions as $key => $value) {

                if ($key != 'year') {
                    $select->where('`'.$key.'` LIKE ?', '%'.$value.'%');
                    continue;
                }

                if ($type == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID) {

                    if (!empty($value['from'])) {
                        $select->where('`year` >= ?', $value['from']);
                    }

                    if (!empty($value['to'])) {
                        $select->where('`year` <= ?', $value['to']);
                    }

                } else {
                    $select->where('from_year <= ?', $value);
                    $select->where('to_year >= ?', $value);
                }
            }

            $filterData = $select->query()->fetchAll();

            if (empty($filterData)) {
                $result[] = array(
                    'id' => $filterId,
                    'type' => 'filter',
                    'note'  => $filter->getNote(),
                    'info'  => array()
                );
                continue;
            }

            if ($type == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID) {

                $groupedData = $this->groupEpidsData($filterData, $conditions);
                foreach ($groupedData as $group) {
                    $result[] = array(
                        'id' => $filterId,
                        'type' => 'filter',
                        'note'  => $filter->getNote(),
                        'info'  => $group
                    );
                }
                continue;
            }

            foreach ($filterData as $item) {
                $result[] = array(
                    'id' => $item[$typeIdentifier],
                    'type' => $typeIdentifier,
                    'note'  => $filter->getNote(),
                    'info'  => $item
                );
            }
        }

        return $result;
    }

    private function prepareRawMotorsGroups($data, $type)
    {
        if (empty($data)) {
            return array();
        }

        $result = array();

        foreach ($data as $groupId) {

            /** @var Ess_M2ePro_Model_Ebay_Motor_Group $group */
            $group = Mage::getModel('M2ePro/Ebay_Motor_Group')->load($groupId);

            if ($group->getType() != $type) {
                continue;
            }

            if (!$group->getId()) {
                $result[] = array(
                    'id' => $groupId,
                    'type' => 'group',
                    'note'  => $group->getNote(),
                    'info'  => array()
                );
                continue;
            }

            if ($group->isModeItem()) {
                $items = $this->prepareRawMotorsItems($group->getItems(), $type);
            } else {
                $items = $this->prepareRawMotorsFilters($group->getFiltersIds(), $type);
            }

            $result = array_merge($result, $items);
        }

        return $result;
    }

    //########################################

    private function getPreparedMotorsEpidsData($data)
    {
        $ebayAttributes = $this->getEbayMotorsEpidsAttributes();

        $preparedData = array();
        $emptySavedItems = array();

        foreach ($data as $item) {

            if (empty($item['info'])) {
                $emptySavedItems[$item['type']][] = $item;
                continue;
            }

            $motorsList = array();
            $motorsData = $this->buildEpidData($item['info']);

            foreach ($motorsData as $key => $value) {

                if ($value == '--') {
                    unset($motorsData[$key]);
                    continue;
                }

                $name = $key;

                foreach ($ebayAttributes as $ebayAttribute) {
                    if ($ebayAttribute['title'] == $key) {
                        $name = $ebayAttribute['ebay_id'];
                        break;
                    }
                }

                $motorsList[] = array(
                    'name'  => $name,
                    'value' => $value
                );
            }

            $preparedData[] = array(
                'list' => $motorsList,
                'note' => $item['note'],
            );
        }

        if (!empty($emptySavedItems['epid'])) {

            $tempItems = array();
            foreach ($emptySavedItems['epid'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__('
                Some ePID(s) which were saved in Parts Compatibility Magento Attribute
                have been removed. Their Values were ignored and not sent on eBay',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        if (!empty($emptySavedItems['filter'])) {

            $tempItems = array();
            foreach ($emptySavedItems['filter'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__('
                Some ePID(s) Grid Filter(s) was removed, that is why its Settings were
                ignored and can not be applied',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        if (!empty($emptySavedItems['group'])) {

            $tempItems = array();
            foreach ($emptySavedItems['group'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__('
                Some ePID(s) Group(s) was removed, that is why its Settings were
                ignored and can not be applied',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        return $preparedData;
    }

    private function getPreparedMotorsKtypesData($data)
    {
        $preparedData = array();
        $emptySavedItems = array();

        foreach ($data as $item) {

            if (empty($item['info'])) {
                $emptySavedItems[$item['type']][] = $item;
                continue;
            }

            $preparedData[] = array(
                'ktype' => $item['id'],
                'note' => $item['note'],
            );
        }

        if (!empty($emptySavedItems['ktype'])) {

            $tempItems = array();
            foreach ($emptySavedItems['ktype'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__('
                Some kTypes(s) which were saved in Parts Compatibility Magento Attribute
                have been removed. Their Values were ignored and not sent on eBay',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        if (!empty($emptySavedItems['filter'])) {

            $tempItems = array();
            foreach ($emptySavedItems['filter'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__('
                Some kTypes(s) Grid Filter(s) was removed, that is why its Settings
                were ignored and can not be applied',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        if (!empty($emptySavedItems['group'])) {

            $tempItems = array();
            foreach ($emptySavedItems['group'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__('
                Some kTypes(s) Group(s) was removed, that is why its Settings were
                ignored and can not be applied',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        return $preparedData;
    }

    // ---------------------------------------

    private function groupEpidsData($data, $condition)
    {
        $groupingFields = array_unique(array_merge(
            array('year', 'make', 'model'),
            array_keys($condition)
        ));

        $groups = array();
        foreach ($data as $item) {
            if (empty($groups)) {

                $group = array();
                foreach ($groupingFields as $groupingField) {
                    $group[$groupingField] = $item[$groupingField];
                }

                ksort($group);

                $groups[] = $group;
                continue;
            }

            $newGroup = array();
            foreach ($groupingFields as $groupingField) {
                $newGroup[$groupingField] = $item[$groupingField];
            }

            ksort($newGroup);

            if (!in_array($newGroup, $groups)) {
                $groups[] = $newGroup;
            }
        }

        return $groups;
    }

    private function buildEpidData($resource)
    {
        $motorsData = array();

        if (isset($resource['make'])) {
            $motorsData['Make'] = $resource['make'];
        }

        if (isset($resource['model'])) {
            $motorsData['Model'] = $resource['model'];
        }

        if (isset($resource['year'])) {
            $motorsData['Year'] = $resource['year'];
        }

        if (isset($resource['submodel'])) {
            $motorsData['Submodel'] = $resource['submodel'];
        }

        if (isset($resource['trim'])) {
            $motorsData['Trim'] = $resource['trim'];
        }

        if (isset($resource['engine'])) {
            $motorsData['Engine'] = $resource['engine'];
        }

        return $motorsData;
    }

    private function getEbayMotorsEpidsAttributes()
    {
        $categoryId = $this->getCategorySource()->getMainCategory();
        $categoryData = $this->getEbayMarketplace()->getCategory($categoryId);

        $features = !empty($categoryData['features']) ?
                    (array)json_decode($categoryData['features'], true) : array();

        $attributes = !empty($features['parts_compatibility_attributes']) ?
                      $features['parts_compatibility_attributes'] : array();

        return $attributes;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    private function getCategoryTemplate()
    {
        if (is_null($this->categoryTemplate)) {
            $this->categoryTemplate = $this->getListingProduct()
                                           ->getChildObject()
                                           ->getCategoryTemplate();
        }
        return $this->categoryTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private function getOtherCategoryTemplate()
    {
        if (is_null($this->otherCategoryTemplate)) {
            $this->otherCategoryTemplate = $this->getListingProduct()
                                                ->getChildObject()
                                                ->getOtherCategoryTemplate();
        }
        return $this->otherCategoryTemplate;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Helper_Component_Ebay_Motors
     */
    private function getMotorsHelper()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motors');
    }

    private function getMotorsAttribute($type)
    {
        return $this->getMotorsHelper()->getAttribute($type);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    private function getCategorySource()
    {
        return $this->getEbayListingProduct()->getCategoryTemplateSource();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory_Source
     */
    private function getOtherCategorySource()
    {
        return $this->getEbayListingProduct()->getOtherCategoryTemplateSource();
    }

    //########################################
}