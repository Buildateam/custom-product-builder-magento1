<?php

// get the entity type id
$entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();

// get default attribute set id
$defaultAttributeSetId = Mage::getModel('catalog/product')->getDefaultAttributeSetId();

$attributeSetName = "kitproductgen";

// create an attribute set
$attributeSet = Mage::getModel('eav/entity_attribute_set')->setEntityTypeId($entityTypeId)->setAttributeSetName($attributeSetName);
$attributeSet->validate();
$attributeSet->save();


// init new attribute set from default
$attributeSet->initFromSkeleton($defaultAttributeSetId)->save();


// creating an attribute

$entitySetup =Mage::getModel('eav/entity_setup','core_setup');

$attributeCode = 'kitproductoption';

// attribute options
$attrOptions = array(
    'position' => 0,
    'user_defined' => true,
    'is_global' => 1,
    'is_configurable' => 1,
    'label' => 'Kit Product Option',
    'type' => 'int',
    'input'=>'select',
    'apply_to'=>'simple,bundle,grouped,configurable',
    'note'=>'',
    'option' => array(
        'values' => array(
           1, 24, 5, 6, 7, 8, 9, 10
        )
    )
);



// create the attribute
$entitySetup->addAttribute('catalog_product', $attributeCode , $attrOptions);

// get attribute id
$attributeId = $entitySetup->getAttributeId('catalog_product',$attributeCode);

//get attribute set id
$attributeSetId = $entitySetup->getAttributeSetId('catalog_product',$attributeSetName);

// get attribute group id
$attributeGroupId = $entitySetup->getAttributeGroupId('catalog_product',$attributeSetId,'General');

//add attribute to an attribute set
 $entitySetup->addAttributeToSet('catalog_product',$attributeSetId,$attributeGroupId,$attributeId);


?>