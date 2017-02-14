<?php

$installer = $this;
$installer->startSetup();

// WYSIWYG hidden by default
Mage::getConfig()->saveConfig('cms/wysiwyg/enabled', 'hidden');

// Enable sidebar cart
Mage::getConfig()->saveConfig('checkout/sidebar/display', '1');

Mage::getSingleton('ultimo/cssgen_generator')->generateCss('grid',   NULL, NULL);
Mage::getSingleton('ultimo/cssgen_generator')->generateCss('layout', NULL, NULL);
Mage::getSingleton('ultimo/cssgen_generator')->generateCss('design', NULL, NULL);

$installer->endSetup();
