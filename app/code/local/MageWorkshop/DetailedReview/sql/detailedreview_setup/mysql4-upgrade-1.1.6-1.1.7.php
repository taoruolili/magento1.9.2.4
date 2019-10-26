<?php

$installer = $this;
$installer->startSetup();

$storesCollection = Mage::getModel('core/store')->getCollection();
$storeIds = array();
foreach($storesCollection as $store) {
    $storeIds[] = $store->getId();
}
$prosConsModel = Mage::getModel('detailedreview/review_proscons');

$prosConsArray = array(
    array('name' => 'Price', 'status' => 1, 'store_ids' => $storeIds, 'entity_type' => MageWorkshop_DetailedReview_Model_Source_EntityType::PROS),
    array('name' => 'Quality', 'status' => 1, 'store_ids' => $storeIds, 'entity_type' => MageWorkshop_DetailedReview_Model_Source_EntityType::PROS),
    array('name' => 'Manufacturer', 'status' => 1, 'store_ids' => $storeIds, 'entity_type' => MageWorkshop_DetailedReview_Model_Source_EntityType::PROS),
    array('name' => 'Price', 'status' => 1, 'store_ids' => $storeIds, 'entity_type' => MageWorkshop_DetailedReview_Model_Source_EntityType::CONS),
    array('name' => 'Quality', 'status' => 1, 'store_ids' => $storeIds, 'entity_type' => MageWorkshop_DetailedReview_Model_Source_EntityType::CONS),
    array('name' => 'Manufacturer', 'status' => 1, 'store_ids' => $storeIds, 'entity_type' => MageWorkshop_DetailedReview_Model_Source_EntityType::CONS)
);

foreach($prosConsArray as $prosCons) {
    $prosConsModel->setData($prosCons)
        ->save();
    $prosConsModel->clearInstance();
}

$installer->addAttribute('catalog_category', 'use_parent_proscons_settings', array(
    'type'                       => 'int',
    'label'                      => 'Use Parent Category Settings for Pros and Cons Fields',
    'input'                      => 'select',
    'source'                     => 'eav/entity_attribute_source_boolean',
    'default'                    => 0,
    'sort_order'                 => 30,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Detailed Review Settings',
));

$installer->addAttribute('catalog_category', 'pros', array(
    'type'                       => 'text',
    'label'                      => 'Pros',
    'input'                      => 'multiselect',
    'source'                     => 'detailedreview/category_attribute_source_pros',
    'backend'                    => 'detailedreview/category_attribute_backend_pros',
    'sort_order'                 => 40,
    'required'                   => 0,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Detailed Review Settings',
));

$installer->addAttribute('catalog_category', 'cons', array(
    'type'                       => 'text',
    'label'                      => 'Cons',
    'input'                      => 'multiselect',
    'source'                     => 'detailedreview/category_attribute_source_cons',
    'backend'                    => 'detailedreview/category_attribute_backend_cons',
    'sort_order'                 => 50,
    'required'                   => 0,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Detailed Review Settings',
));

$entityTypeId     = $installer->getEntityTypeId(Mage_Catalog_Model_Category::ENTITY);
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, 'Detailed Review Settings');

$attributes = array(
    'review_fields_available' => 20
);

foreach ($attributes as $attributeCode => $sortOrder) {
    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        $attributeCode,
        $sortOrder
    );
}

$this->endSetup();