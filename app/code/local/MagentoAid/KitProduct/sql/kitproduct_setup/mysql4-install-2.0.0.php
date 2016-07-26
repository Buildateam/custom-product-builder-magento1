<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `catalog_product_kitproduct_option` (
  `option_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Option Id',
  `parent_id` int(10) unsigned NOT NULL COMMENT 'Parent Id',
  `required` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Required',
  `position` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Position',
  `type` varchar(255) DEFAULT NULL COMMENT 'Type',
  PRIMARY KEY (`option_id`),
  KEY `IDX_CATALOG_PRODUCT_KITPRODUCT_OPTION_PARENT_ID` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Catalog Product KitProduct Option' AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `catalog_product_kitproduct_option_value` (
  `value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Value Id',
  `option_id` int(10) unsigned NOT NULL COMMENT 'Option Id',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  `title` varchar(255) DEFAULT NULL COMMENT 'Title',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CATALOG_PRODUCT_KITPRODUCT_OPTION_VALUE_OPTION_ID_STORE_ID` (`option_id`,`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Catalog Product KitProduct Option Value' AUTO_INCREMENT=218 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `catalog_product_kitproduct_product` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL,
  `depends_on` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `product_id_2` (`product_id`),
  KEY `depends_on` (`depends_on`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=210 ;

-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `catalog_product_kitproduct_selection` (
  `selection_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Selection Id',
  `option_id` int(10) unsigned NOT NULL COMMENT 'Option Id',
  `parent_product_id` int(10) unsigned NOT NULL COMMENT 'Parent Product Id',
  `product_id` int(10) unsigned NOT NULL COMMENT 'Product Id',
  `position` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Position',
  `is_default` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Is Default',
  `selection_price_type` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Selection Price Type',
  `selection_price_value` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Selection Price Value',
  `selection_qty` decimal(12,4) DEFAULT NULL COMMENT 'Selection Qty',
  `selection_can_change_qty` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Selection Can Change Qty',
  `image` varchar(255) DEFAULT NULL,
  `data_serialized` text NOT NULL,
  PRIMARY KEY (`selection_id`),
  UNIQUE KEY `PARENT_PRODUCT__PRODUCT` (`parent_product_id`,`product_id`),
  KEY `IDX_CATALOG_PRODUCT_KITPRODUCT_SELECTION_OPTION_ID` (`option_id`),
  KEY `IDX_CATALOG_PRODUCT_KITPRODUCT_SELECTION_PRODUCT_ID` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Catalog Product KitProduct Selection' AUTO_INCREMENT=33 ;

-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `catalog_product_kitproduct_selection_price` (
  `selection_id` int(10) unsigned NOT NULL COMMENT 'Selection Id',
  `website_id` smallint(5) unsigned NOT NULL COMMENT 'Website Id',
  `selection_price_type` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Selection Price Type',
  `selection_price_value` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Selection Price Value',
  PRIMARY KEY (`selection_id`,`website_id`),
  KEY `IDX_CATALOG_PRODUCT_KITPRODUCT_SELECTION_PRICE_WEBSITE_ID` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Product KitProduct Selection Price';

ALTER TABLE `catalog_product_kitproduct_option`
  ADD CONSTRAINT `FK_CAT_PRD_KITPRD_OPT_PARENT_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`parent_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `catalog_product_kitproduct_option_value`
  ADD CONSTRAINT `FK_CAT_PRD_KITPRD_OPT_VAL_OPT_ID_CAT_PRD_KITPRD_OPT_OPT_ID` FOREIGN KEY (`option_id`) REFERENCES `catalog_product_kitproduct_option` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `catalog_product_kitproduct_product`
  ADD CONSTRAINT `catalog_product_kitproduct_product_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `catalog_product_kitproduct_selection`
  ADD CONSTRAINT `FK_CAT_PRD_KITPRD_SELECTION_OPT_ID_CAT_PRD_KITPRD_OPT_OPT_ID` FOREIGN KEY (`option_id`) REFERENCES `catalog_product_kitproduct_option` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_CAT_PRD_KITPRD_SELECTION_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `catalog_product_kitproduct_selection_price`
  ADD CONSTRAINT `FK_83E578F871DC59510C543778B35E653B` FOREIGN KEY (`selection_id`) REFERENCES `catalog_product_kitproduct_selection` (`selection_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_CAT_PRD_KITPRD_SELECTION_PRICE_WS_ID_CORE_WS_WS_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE;

");


/**
 * Add attributes to the eav/attribute 
 */
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'price_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '',
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
        'used_in_product_listing' => true,
        'unique'            => false,
        'apply_to'          => 'kitproduct',
        'is_configurable'   => false
    ));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'sku_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '',
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

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'weight_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '',
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
        'used_in_product_listing' => true,
        'unique'            => false,
        'apply_to'          => 'kitproduct',
        'is_configurable'   => false
    ));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'price_view', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Price View',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'kitproduct/product_attribute_source_price_view',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => true,
        'user_defined'      => false,
        'default'           => '',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'used_in_product_listing' => true,
        'unique'            => false,
        'apply_to'          => 'kitproduct',
        'is_configurable'   => false
    ));

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'shipment_type', array(
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
        'used_in_product_listing' => true,
        'unique'            => false,
        'apply_to'          => 'kitproduct',
        'is_configurable'   => false
    ));

	
	
	
$applyTo = explode(',', $installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'group_price', 'apply_to'));
if (!in_array('kitproduct', $applyTo)) {
    $applyTo[] = 'kitproduct';
    $installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'group_price', 'apply_to', implode(',', $applyTo));
}	






$installer->addAttribute('catalog_product', 'price_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '',
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

$installer->addAttribute('catalog_product', 'sku_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '',
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

$installer->addAttribute('catalog_product', 'weight_type', array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '',
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

$installer->addAttribute('catalog_product', 'price_view', array(
        'group'             => 'Prices',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Price View',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'kitproduct/product_attribute_source_price_view',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
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

$fieldList = array('price','special_price','special_from_date','special_to_date',
    'minimal_price','cost','tier_price','weight','tax_class_id');
foreach ($fieldList as $field) {
    $applyTo = explode(',', $installer->getAttribute('catalog_product', $field, 'apply_to'));
    if (!in_array('kitproduct', $applyTo)) {
        $applyTo[] = 'kitproduct';
        $installer->updateAttribute('catalog_product', $field, 'apply_to', join(',', $applyTo));
    }
}

$installer->endSetup();
