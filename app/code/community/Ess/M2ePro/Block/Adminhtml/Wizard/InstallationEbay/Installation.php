<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_Abstract
{
    //########################################

    abstract protected function getStep();

    //########################################

    protected function _beforeToHtml()
    {
        // Initialization block
        // ---------------------------------------
        $this->setId('wizard' . $this->getNick() . $this->getStep());
        // ---------------------------------------

        $this->setTemplate('widget/form/container.phtml');

        // ---------------------------------------
        return parent::_beforeToHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $urls = json_encode(Mage::helper('M2ePro')->getControllerActions('adminhtml_wizard_installationEbay'));

        $additionalJs = <<<SCRIPT
<script type="text/javascript">
    M2ePro.url.add({$urls});
    InstallationEbayWizardObj = new WizardInstallationEbay();
</script>
SCRIPT;

        $contentBlock = Mage::helper('M2ePro/Module_Wizard')->createBlock(
            'installation_' . $this->getStep() . '_content',
            $this->getNick()
        );

        return parent::_toHtml() .
               $additionalJs .
               $contentBlock->toHtml();
    }

    //########################################
}