<?php

$installer = $this;
$installer->startSetup();



Mage::getSingleton('ultimo/cssgen_generator')->generateCss('grid',   NULL, NULL);
Mage::getSingleton('ultimo/cssgen_generator')->generateCss('layout', NULL, NULL);
Mage::getSingleton('ultimo/cssgen_generator')->generateCss('design', NULL, NULL);



$version = Mage::getConfig()->getModuleConfig('Infortis_Ultimo')->version;
Mage::log("[Ultimo " . $version . "] Setup finished.", null, "Infortis_Ultimo.log");



$installer->endSetup();
