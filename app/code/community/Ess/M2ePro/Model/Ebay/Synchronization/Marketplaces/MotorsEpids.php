<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_MotorsEpids
    extends Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/motors_epids/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Parts Compatibility';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function isPossibleToRun()
    {
        if (!parent::isPossibleToRun()) {
            return false;
        }

        $params = $this->getParams();

        return Mage::helper('M2ePro/Component_Ebay_Motors')
                    ->isMarketplaceSupportsEpid($params['marketplace_id']);
    }

    protected function performActions()
    {
        $partNumber = 1;
        $this->deleteAllSpecifics();

        for ($i = 0; $i < 100; $i++) {

            $this->getActualLockItem()->setPercents($this->getPercentsStart());

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get_motor','Get Motor specifics from eBay');
            $response = $this->receiveFromEbay($partNumber);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get_motor');

            if (empty($response)) {
                break;
            }

            $this->getActualLockItem()->setStatus(
                'Processing data ('.(int)$partNumber.'/'.(int)$response['total_parts'].')'
            );
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
            $this->getActualLockItem()->activate();

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save_motor','Save specifics to DB');
            $this->saveSpecificsToDb($response['data']);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save_motor');

            $this->getActualLockItem()->setPercents($this->getPercentsEnd());
            $this->getActualLockItem()->activate();

            $partNumber = $response['next_part'];

            if (is_null($partNumber)) {
                break;
            }
        }

        $this->logSuccessfulOperation();
    }

    //########################################

    protected function receiveFromEbay($partNumber)
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('marketplace','get','motorsEpids',
                                                            array('part_number' => $partNumber));

        $response = $dispatcherObj->process($connectorObj);

        if (is_null($response) || empty($response['data'])) {
            $response = array();
        }

        $dataCount = isset($response['data']) ? count($response['data']) : 0;
        $this->getActualOperationHistory()->addText("Total received parts from eBay: {$dataCount}");

        return $response;
    }

    protected function deleteAllSpecifics()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsEpids = Mage::getSingleton('core/resource')
                                        ->getTableName('m2epro_ebay_dictionary_motor_epid');

        $connWrite->delete($tableMotorsEpids, '`is_custom` = 0');
    }

    protected function saveSpecificsToDb(array $data)
    {
        $totalCountItems = count($data['items']);
        if ($totalCountItems <= 0) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsEpids = Mage::getSingleton('core/resource')
                                        ->getTableName('m2epro_ebay_dictionary_motor_epid');

        $iteration            = 0;
        $iterationsForOneStep = 1000;
        $percentsForOneStep   = ($this->getPercentsInterval()/2) / ($totalCountItems/$iterationsForOneStep);

        $temporaryIds   = array();
        $itemsForInsert = array();

        for ($i = 0; $i < $totalCountItems; $i++) {

            $item = $data['items'][$i];
            $temporaryIds[] = $item['ePID'];

            $itemsForInsert[] = array(
                'epid'         => $item['ePID'],
                'product_type' => (int)$item['product_type'],
                'make'         => $item['Make'],
                'model'        => $item['Model'],
                'year'         => $item['Year'],
                'trim'         => (isset($item['Trim']) ? $item['Trim'] : NULL),
                'engine'       => (isset($item['Engine']) ? $item['Engine'] : NULL),
                'submodel'     => (isset($item['Submodel']) ? $item['Submodel'] : NULL)
            );

            if (count($itemsForInsert) >= 100 || $i >= ($totalCountItems - 1)) {

                $connWrite->insertMultiple($tableMotorsEpids, $itemsForInsert);
                $connWrite->delete($tableMotorsEpids, array('is_custom = ?' => 1,
                                                                'epid IN (?)'   => $temporaryIds));
                $itemsForInsert = $temporaryIds = array();
            }

            if (++$iteration % $iterationsForOneStep == 0) {
                $percentsShift = ($iteration/$iterationsForOneStep) * $percentsForOneStep;
                $this->getActualLockItem()->setPercents(
                    $this->getPercentsStart() + $this->getPercentsInterval()/2 + $percentsShift
                );
            }
        }
    }

    protected function logSuccessfulOperation()
    {
        // M2ePro_TRANSLATIONS
        // The "Parts Compatibility" Action for eBay Motors Site has been successfully completed.
        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Parts Compatibility" Action for eBay Motors Site has been successfully completed.'
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //########################################
}