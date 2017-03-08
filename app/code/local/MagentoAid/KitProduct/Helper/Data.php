<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is kitproductd with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * KitProduct helper
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MagentoAid_KitProduct_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_NODE_KITPRODUCT_PRODUCT_TYPE = 'global/catalog/product/type/kitproduct';

    const ATTRIBUTE_SET_KIT = "kitproductgen";

    const ATTRIBUTE_KIT = "kitproductoption";

    /**
     * Add a simple product to configurable product
     * @param $subgroup_id - id of configurable product
     * @param $option_ids  - array of ids of simple products.
     */
    public function addOptionsToSubgroup($subgroup_id, $option_ids){

        $configurable = Mage::getModel('catalog/product')->load($subgroup_id);

        $simpleIds = array();

        foreach($option_ids as $option_id){
            $simpleIds[] = $option_id;
        }


        // attach simple products to configurable product
        if ( $configurable->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ) {
            Mage::getResourceModel('catalog/product_type_configurable')
                ->saveProducts($configurable, $simpleIds);
        }

        // reload configurable product to get prices
        $configurable = Mage::getModel('catalog/product')->load($subgroup_id);
        $attribute = Mage::getModel('catalog/product_type_configurable')->getConfigurableAttributes($configurable)->getFirstItem();
        $configPrices = $attribute->getPrices();

        if (!$configPrices) {
            return;
        }

        foreach($option_ids as $option_id){
            $simple = Mage::getModel('catalog/product')->load($option_id);
            foreach($configPrices as $k => $configPrice){
                if($configPrice['value_index'] == $simple->getData(MagentoAid_KitProduct_Helper_Data::ATTRIBUTE_KIT)){
                    $configPrices[$k]['pricing_value'] = $simple->getFinalPrice();
                }
            }
        }

        $attribute->setValues($configPrices);
        Mage::getResourceModel('catalog/product_type_configurable_attribute')->savePrices($attribute);

    }

    /**
     * Get the optionId from value
     * @param $value - attribute option value
     * @return int - option Id
     * @throws Mage_Core_Exception
     */
    public function getOptionId($value){
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', MagentoAid_KitProduct_Helper_Data::ATTRIBUTE_KIT);
        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        }

        foreach($options as $option){
            if(trim($option['label']) == trim($value)){
                return $option['value'];
            }
        }

        return 0;
    }

    /**
     * Add option value to attribute
     * @param $attributeValue
     */
    public function addOption($attributeValue){
        $attr_model = Mage::getModel('catalog/resource_eav_attribute');
        $attr_model->load($this->_getKitAttributeId());
        $values = array(
            $this->getOptionId($attributeValue) => array(
               $attributeValue, $attributeValue
            ),
        );

        $data['option']['value'] = $values;
        $attr_model->addData($data);
        $attr_model->save();
    }

    /**
     * Retrieve array of allowed product types for kitproduct selection product
     *
     * @return array
     */
    public function getAllowedSelectionTypes()
    {
        $config = Mage::getConfig()->getNode(self::XML_NODE_KITPRODUCT_PRODUCT_TYPE);
        return array_keys($config->allowed_selection_types->asArray());
    }

    public function loadStock(&$product)
    {
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($product->getId());
        }
        $product->setStockItem($stockItem);
    }

    /**
     * Create a simple product
     * @param $name product's name
     * @param $groupTitle group's title
     * @param $subGroupTitle subgroup's title
     * @param $optionTitle option's title
     * @param $thumbnailImage path for thumbmail image
     * @param $baseImage path for base/small image
     * @param $optionId for attribute
     * @return mixed
     * @throws Exception
     */
    public function createSimpleProduct($productId, $name, $groupTitle, $subGroupTitle, $optionTitle, $thumbnailImage, $baseImage, $optionId, $price)
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $product = $this->_createProduct($productId, $name, Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $groupTitle, $subGroupTitle, $optionTitle, $thumbnailImage, $baseImage, $optionId);
        $product->setPrice($price);
        $product->save();

        if ( $option = Mage::registry('kit_option') )
        if ( $product->getEntityId() )
        {
            $kp = $this->createOrUpdateKitproductOptionProduct( $product->getEntityId(), $option );
        }

        return $product;
    }

    protected function createOrUpdateKitproductOptionProduct( $productId, $option )
    {
        $kp = Mage::getModel('kitproduct/product')->load($productId, 'product_id');
        if ( !$kp->getItemId() )  // create new
        {
            $kp->setProductId($productId);
        }

        if ( isset($option['position']) )
        {
            $kp->setPosition($option['position']);
        }
        if ( isset($option['depends_on']) )
        {
            $kp->setDependsOn($option['depends_on']);
        }

        $kp->save();
        return $kp;
    }

    /**
     * Create a configurable product
     * @param $name product's name
     * @param $groupTitle group's title
     * @param $subGroupTitle subgroup's title
     * @return Mage_Catalog_Model_Product
     * @throws Exception
     */
    public function createConfigurableProduct($productId, $name, $groupTitle, $subGroupTitle)
    {
        //Mage::log( $this->_getKitAttributeId() );  // 162  kitproductoption

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $product = $this->_createProduct($productId, $name, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $groupTitle, $subGroupTitle);
        if ( $productId )
        {

        }
        else
        {
            $product->getTypeInstance()->setUsedProductAttributeIds(array($this->_getKitAttributeId()));
        }
        $configurableAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray();
        $product->setCanSaveConfigurableAttributes(true);
        $product->setConfigurableAttributesData($configurableAttributesData);
        $product->save();
        return $product;
    }

    /**
     * Get kit attribute set id
     * @return int
     * @throws Mage_Core_Exception
     */
    protected function _getKitAttributeSetId(){
        $entitySetup =Mage::getModel('eav/entity_setup','core_setup');
        return $entitySetup->getAttributeSetId('catalog_product', MagentoAid_KitProduct_Helper_Data::ATTRIBUTE_SET_KIT);
    }

    /**
     * Get kit attribute set id
     * @return int
     * @throws Mage_Core_Exception
     */
    protected function _getKitAttributeId(){
        $entitySetup =Mage::getModel('eav/entity_setup','core_setup');
        return $entitySetup->getAttributeId('catalog_product', MagentoAid_KitProduct_Helper_Data::ATTRIBUTE_KIT);
    }

    protected function _createProduct($productId, $name, $type = 'simple', $groupTitle = '', $subGroupTitle = '', $optionTitle = '', $thumbnailImage = '', $baseImage = '', $optionId = 0)
    {
        //$website_id = Mage::app()->getStore()->getWebsiteId();
        $website = Mage::getModel('core/website')->load(1, 'is_default');
        $website_id = $website->getWebsiteId();

        $product = Mage::getModel('catalog/product');
        if ( $productId )
        {
            $product = Mage::getModel('catalog/product')->load( $productId );
        }

        $sku = $name . "_" . $groupTitle . "_" . $subGroupTitle;

        if ($type == 'simple') {
            $add2sku = "_" . $optionTitle;
            if ( $orient = Mage::helper('kitproduct/option_product')->getOrientByProductName($optionTitle) )
            {
                $add2sku = "_" . $orient;
            }

            $sku .= $add2sku;
            $product->setData(MagentoAid_KitProduct_Helper_Data::ATTRIBUTE_KIT, $optionId);
        }

        $sku = strtolower($sku);
        $sku = str_replace(' ', '', $sku);

        $sku = str_replace('_orientation_', '_o_', $sku);

        $product
            ->setWebsiteIds(array($website_id))
            ->setAttributeSetId($this->_getKitAttributeSetId())
            ->setCreatedAt(strtotime('now'))
            ->setName($name)
            ->setSku($sku)
            ->setStatus(1)
            ->setTaxClassId(0)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
            ->setStockData(array(
                'manage_stock' => 0,
                'is_in_stock' => 1
            ))
            ->setMediaGallery(array('images' => array(), 'values' => array()))
            ->setTypeId($type);

        if ( !$productId )
        {
            $product->setDescription( $optionTitle . ' description text' );
            $product->setShortDescription( $optionTitle . ' description text' );
        }

        if($type == 'simple') {
            // Add thumbnail image
            if (file_exists($thumbnailImage)) {
                $product->addImageToMediaGallery($thumbnailImage, 'thumbnail', false, false);
            }

            // add base/small image
            if (file_exists($baseImage)) {
                $product->addImageToMediaGallery($baseImage, array('image', 'small_image'), false, false);
            }

            $product->setName( $optionTitle );
            $product->setWeight( 0 );



            if ( $option = Mage::registry('kit_option') )
            if ( !empty($option['depends_on']) )
            if ( $orientOptions = Mage::registry('orientation_options') )
            {
                $dependsOn = $option['depends_on'];
                if ( isset($orientOptions[$dependsOn]) )
                {
                    $add2sku = '_o_' . strtolower($orientOptions[$dependsOn]);
                    //Mage::log( $add2sku );
                    $product->setSku( $product->getSku() . $add2sku );
                }
            }

        }
        else
        if ( $type == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE )
        {
            $product->setName( $subGroupTitle );
        }


        return $product;
    }
}
