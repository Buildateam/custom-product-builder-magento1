<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();

$installer->addAttribute('catalog_product', 'shipment_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Shipment',
        'input'             => '',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => false,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'kitproduct',
        'is_configurable'   => false
    ));


$installer->run("
    UPDATE `{$installer->getTable('catalog/product')}` SET `has_options` = '1'
    WHERE (entity_id IN (
        SELECT parent_product_id FROM `{$installer->getTable('kitproduct/selection')}` GROUP BY parent_product_id
    ));
");

$installer->updateAttribute('catalog_product', 'price_type', 'used_in_product_listing', 1);

$installer->updateAttribute('catalog_product', 'price_view', 'used_in_product_listing', 1);
$installer->updateAttribute('catalog_product', 'shipment_type', 'used_in_product_listing', 1);
$installer->updateAttribute('catalog_product', 'weight_type', 'used_in_product_listing', 1);


$attributes = array(
    $installer->getAttributeId('catalog_product', 'cost')
);

$sql    = $installer->getConnection()->quoteInto("SELECT * FROM `{$installer->getTable('catalog/eav_attribute')}` WHERE attribute_id IN (?)", $attributes);
$data   = $installer->getConnection()->fetchAll($sql);

foreach ($data as $row) {
    $row['apply_to'] = array_flip(explode(',', $row['apply_to']));
    unset($row['apply_to']['kitproduct']);
    $row['apply_to'] = implode(',', array_flip($row['apply_to']));

    $installer->run("UPDATE `{$installer->getTable('catalog/eav_attribute')}`
                SET `apply_to` = '{$row['apply_to']}'
                WHERE `attribute_id` = {$row['attribute_id']}");
}

$installer->run("
INSERT IGNORE INTO `{$installer->getTable('catalog/product_relation')}`
SELECT
  `parent_product_id`,
  `product_id`
FROM `{$installer->getTable('kitproduct/selection')}`;
");

$installer->endSetup();
