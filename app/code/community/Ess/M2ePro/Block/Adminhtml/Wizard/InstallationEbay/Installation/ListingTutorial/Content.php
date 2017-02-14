<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_ListingTutorial_Content
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationListingTutorial');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/installationEbay/installation/listing_tutorial.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $nextStep = Mage::helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getNextStep();

        $onClick = <<<JS
WizardHandlerObj.setStep('{$nextStep}',setLocation.bind(window, location.href));
JS;

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => $onClick,
                'class'   => 'continue_button'
            ));
        $this->setChild('continue_button', $buttonBlock);

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}