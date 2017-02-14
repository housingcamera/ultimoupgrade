<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Abstract extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    protected function _toHtml()
    {
        $initializationBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_wizard_initialization',
            '',
            array('nick'=>$this->getNick())
        );

        return parent::_toHtml() . $initializationBlock->toHtml();
    }

    //########################################
}