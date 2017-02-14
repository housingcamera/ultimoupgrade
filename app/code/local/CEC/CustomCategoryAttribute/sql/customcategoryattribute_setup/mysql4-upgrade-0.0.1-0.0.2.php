<?php 
$installer = $this;
$installer->startSetup();
  
$installer->addAttribute('catalog_category', 'external_url',  array(
    'type'     => 'text',
    'label'    => 'Redirect to External URL',
    'input'    => 'text',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => 0
));
 
 
$installer->endSetup();