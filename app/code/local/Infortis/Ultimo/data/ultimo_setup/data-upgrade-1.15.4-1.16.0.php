<?php

$installer = $this;
$installer->startSetup();



// In this version there are new options to set maximum page width.
// Update the fields based on values configured in previous versions.
$oldWidth = Mage::getStoreConfig('ultimo_layout/responsive/max_width');
$newWidth = NULL;
$newCustomWidth = NULL;

switch ($oldWidth)
{
	case '960':
		$newWidth = '992';
		break;
	case '1280':
		$newWidth = 'custom';
		$newCustomWidth = '1200';
		break;
	case '1360':
		$newWidth = 'custom';
		$newCustomWidth = '1300';
		break;
	// case '1440':
	// 	break;
	// case '1680':
	// 	break;
	// case 'custom':
	// 	break;
}

if ($newWidth !== NULL)
{
	Mage::getConfig()->saveConfig('ultimo_layout/responsive/max_width', $newWidth);
}

if ($newCustomWidth !== NULL)
{
	Mage::getConfig()->saveConfig('ultimo_layout/responsive/max_width_custom', $newCustomWidth);
}



Mage::log("[Ultimo 1.16.0]", null, "Infortis_Ultimo.log");



$installer->endSetup();
