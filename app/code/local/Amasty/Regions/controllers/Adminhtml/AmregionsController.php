<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */
class Amasty_Regions_Adminhtml_AmregionsController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Shipping Areas'));
        $this->_addContent(
            $this->getLayout()->createBlock('amregions/adminhtml_region')
        );
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->_getModel($id);
        Mage::register('amregions_region', $model);
        $this->loadLayout();

        $this->_addContent(
            $this->getLayout()->createBlock('amregions/adminhtml_region_edit')
        );

        $this->renderLayout();
    }

    public function saveAction()
    {
        if($data = $this->getRequest()->getPost())
        {
            $id = (int)$this->getRequest()->getParam('id');
            $model = $this->_getModel($id);
            $model->addData($data);

            try {
                $model->save();
                if(isset($data['country']) && is_array($data['country'])) {
                    $model->setCountries(array_keys($data['country']));
                }


                $this->_getSession()->addSuccess($this->__('The Shipping Area has been saved.'));
                $this->_getSession()->setFormData(false);
                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('continue')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current'=>true));
                    return;
                }

                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('An error occurred while saving the Shipping Area.').$e->getMessage());
            }

            $this->_getSession()->setFormData($data);

            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }



        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->_getModel($id);
        try {
            $model->delete();
            $this->_getSession()->addSuccess($this->__('The Shipping Area has been deleted.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while deleting the Shipping Area.'));
        }

        $this->_redirect('*/*/');
    }

    public function gridAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->_getModel($id);
        Mage::register('amregions_region', $model);

        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('amregions/adminhtml_region_grid')->toHtml()
        );
    }

    public function massDeleteAction()
    {
        $deleteIds = $this->getRequest()->getParam('region_ids');
        if(!is_array($deleteIds)) {
            $this->_getSession()->addError($this->__('Please select Shipping Area(s).'));
        } else {
            try {
                $collection = $this->_getModel()->getCollection()->addFieldToFilter("region_id", array('in'=>$deleteIds));
                $collection->walk('delete');
                $this->_getSession()->addSuccess($this->__('Shipping Area(s) has been deleted.'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }

        }
        $this->_redirect('*/*/');
    }



    /**
     * @param null $id
     *
     * @return Amasty_Regions_Model_Region
     */
    protected function _getModel($id = null)
    {
        $model = Mage::getModel('amregions/region');

        return is_null($id) ? $model : $model->load($id);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/amregions');
    }
}