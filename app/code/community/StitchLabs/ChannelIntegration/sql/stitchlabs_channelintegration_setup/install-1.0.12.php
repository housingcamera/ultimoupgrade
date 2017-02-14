<?php

$installer  = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('sales/creditmemo_item'),
        'StitchLabs_ChannelIntegration_return_to_stock',
        'tinyint(1) unsigned NOT NULL DEFAULT 0'
    );

$installer->addAttribute(
    'creditmemo_item',
    'StitchLabs_ChannelIntegration_return_to_stock',
    [
        'type'   => 'int',
        'grid'   => true,
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'default' => '0',
    ]
);

$installer->endSetup();