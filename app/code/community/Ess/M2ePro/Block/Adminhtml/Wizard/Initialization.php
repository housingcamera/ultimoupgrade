<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Initialization extends Mage_Adminhtml_Block_Template
{
    //########################################

    protected function _beforeToHtml()
    {
        // Set data for form
        // ---------------------------------------
        $this->addData(array(
            'step' => $this->helper('M2ePro/Module_Wizard')->getStep($this->getNick()),
            'steps' => json_encode($this->helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getSteps()),
            'status' => $this->helper('M2ePro/Module_Wizard')->getStatus($this->getNick())
        ));
        // ---------------------------------------

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInitialization');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/initialization.phtml');

        // ---------------------------------------
        return parent::_beforeToHtml();
    }

    //########################################
}