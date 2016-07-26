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
 * KitProduct Products Observer
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MagentoAid_KitProduct_Model_Observer
{

    /**
     * Return module's helper
     * @return MagentoAid_KitProduct_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('kitproduct');
    }

    /*
    public function productBeforeSave(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == 'kitproduct') {

            $groups = $product->getData('kit_product_options_data');
            $subgroups = $product->getData('kit_product_selections_data');

            $subgroupName = '';

            foreach ($groups as $k => $group) {

                foreach ($subgroups[$k] as $x => $subgroup) {
                    if (isset($subgroup['name'])) { // is a sub group

                       $productGenerated = $this->_getHelper()->createConfigurableProduct($subgroup['name'], $group['title'], $subgroup['name']);

                        if (count($subgroup) > 2) {
                            foreach ($subgroup as $v => $option) {
                                if (is_int($v)) {
                                    $optionId = $this->_getHelper()->getOptionId($option['title']);
                                    if ($optionId == 0) {
                                        $this->_getHelper()->addOption($option['title']);
                                        $optionId = $this->_getHelper()->getOptionId($option['title']);
                                    }
                                    $price = $option['price'];

                                }

                            }
                        }
                        $subgroupName = $subgroup['name'];


                    } else {
                        foreach ($subgroup as $v => $option) {
                            $optionId = $this->_getHelper()->getOptionId($option['title']);
                            if ($optionId == 0) {
                                $this->_getHelper()->addOption($option['title']);
                                $optionId = $this->_getHelper()->getOptionId($option['title']);
                            }
                            $price = $option['price'];
                            if (isset($_FILES['kitproduct_selections']['name'][$k][$x][$v])) {
                                $names = $_FILES['kitproduct_selections']['name'][$k][$x][$v];
                                $files = $_FILES['kitproduct_selections']['tmp_name'][$k][$x][$v];
                                $imagePath = $files['image'];
                                $iconPath = $files['icon'];

                                $path = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/catalog/kitproduct/';

                                if (!is_dir($path)) {
                                    mkdir($path, 0777);
                                }

                                $pathMoveIcon = $path . $names['icon'];
                                $pathMoveImage = $path . $names['image'];

                                move_uploaded_file($iconPath, $pathMoveIcon);
                                move_uploaded_file($imagePath, $pathMoveImage);
                            }
                            $this->_getHelper()->createSimpleProduct($option['title'], $group['title'], $subgroupName, $option['title'], $pathMoveImage, $pathMoveIcon, $optionId);
                        }
                    }

                }

            }
            exit;
        }
        return $this;
    }*/

    /**
     * Setting KitProduct Items Data to product for father processing
     *
     * @param Varien_Object $observer
     * @return MagentoAid_KitProduct_Model_Observer
     */
    public function prepareProductSave($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $product = $observer->getEvent()->getProduct();

        if (($items = $request->getPost('kitproduct_options')) && !$product->getCompositeReadonly()) {
            $product->setKitProductOptionsData($items);
        }

        if (($selections = $request->getPost('kitproduct_selections')) && !$product->getCompositeReadonly()) {
            $product->setKitProductSelectionsData($selections);
        }

        if ($product->getPriceType() == '0' && !$product->getOptionsReadonly()) {
            $product->setCanSaveCustomOptions(true);
            if ($customOptions = $product->getProductOptions()) {
                foreach (array_keys($customOptions) as $key) {
                    $customOptions[$key]['is_delete'] = 1;
                }
                $product->setProductOptions($customOptions);
            }
        }

        $product->setCanSaveKitProductSelections(
            (bool)$request->getPost('affect_kitproduct_product_selections') && !$product->getCompositeReadonly()
        );

        return $this;
    }

    /**
     * Append kitproducts in upsell list for current product
     *
     * @param Varien_Object $observer
     * @return MagentoAid_KitProduct_Model_Observer
     */
    public function appendUpsellProducts($observer)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        /**
         * Check is current product type is allowed for kitproduct selection product type
         */
        if (!in_array($product->getTypeId(), Mage::helper('kitproduct')->getAllowedSelectionTypes())) {
            return $this;
        }

        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection */
        $collection = $observer->getEvent()->getCollection();
        $limit = $observer->getEvent()->getLimit();
        if (is_array($limit)) {
            if (isset($limit['upsell'])) {
                $limit = $limit['upsell'];
            } else {
                $limit = 0;
            }
        }

        /* @var $resource MagentoAid_KitProduct_Model_Mysql4_Selection */
        $resource = Mage::getResourceSingleton('kitproduct/selection');

        $productIds = array_keys($collection->getItems());
        if (!is_null($limit) && $limit <= count($productIds)) {
            return $this;
        }

        // retrieve kitproduct product ids
        $kitproductIds = $resource->getParentIdsByChild($product->getId());
        // exclude up-sell product ids
        $kitproductIds = array_diff($kitproductIds, $productIds);

        if (!$kitproductIds) {
            return $this;
        }

        /* @var $kitproductCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $kitproductCollection = $product->getCollection()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter()
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents();

        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($kitproductCollection);

        if (!is_null($limit)) {
            $kitproductCollection->setPageSize($limit);
        }
        $kitproductCollection->addFieldToFilter('entity_id', array('in' => $kitproductIds))
            ->setFlag('do_not_use_category_id', true);

        if ($collection instanceof Varien_Data_Collection) {
            foreach ($kitproductCollection as $item) {
                $collection->addItem($item);
            }
        } elseif ($collection instanceof Varien_Object) {
            $items = $collection->getItems();
            foreach ($kitproductCollection as $item) {
                $items[$item->getEntityId()] = $item;
            }
            $collection->setItems($items);
        }

        return $this;
    }

    /**
     * Append selection attributes to selection's order item
     *
     * @param Varien_Object $observer
     * @return MagentoAid_KitProduct_Model_Observer
     */
    public function appendKitProductSelectionData($observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();

        if ($attributes = $quoteItem->getProduct()->getCustomOption('kitproduct_selection_attributes')) {
            $productOptions = $orderItem->getProductOptions();
            $productOptions['kitproduct_selection_attributes'] = $attributes->getValue();
            $orderItem->setProductOptions($productOptions);
        }

        return $this;
    }

    /**
     * Add price index data for catalog product collection
     * only for front end
     *
     * @param Varien_Event_Observer $observer
     * @return MagentoAid_KitProduct_Model_Observer
     */
    public function loadProductOptions($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $collection->addPriceData();

        return $this;
    }

    /**
     * duplicating kitproduct options and selections
     *
     * @param Varien_Object $observer
     * @return MagentoAid_KitProduct_Model_Observer
     */
    public function duplicateProduct($observer)
    {
        $product = $observer->getEvent()->getCurrentProduct();

        if ($product->getTypeId() != MagentoAid_KitProduct_Model_Product_Type::TYPE_KITPRODUCT) {
            //do nothing if not kitproduct
            return $this;
        }

        $newProduct = $observer->getEvent()->getNewProduct();

        $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
        $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
            $product->getTypeInstance(true)->getOptionsIds($product),
            $product
        );
        $optionCollection->appendSelections($selectionCollection);

        $optionRawData = array();
        $selectionRawData = array();

        $i = 0;
        foreach ($optionCollection as $option) {
            $optionRawData[$i] = array(
                'required' => $option->getData('required'),
                'position' => $option->getData('position'),
                'type' => $option->getData('type'),
                'title' => $option->getData('title') ? $option->getData('title') : $option->getData('default_title'),
                'delete' => ''
            );
            foreach ($option->getSelections() as $selection) {
                $selectionRawData[$i][] = array(
                    'product_id' => $selection->getProductId(),
                    'position' => $selection->getPosition(),
                    'is_default' => $selection->getIsDefault(),
                    'selection_price_type' => $selection->getSelectionPriceType(),
                    'selection_price_value' => $selection->getSelectionPriceValue(),
                    'selection_qty' => $selection->getSelectionQty(),
                    'selection_can_change_qty' => $selection->getSelectionCanChangeQty(),
                    'delete' => ''
                );
            }
            $i++;
        }

        $newProduct->setKitProductOptionsData($optionRawData);
        $newProduct->setKitProductSelectionsData($selectionRawData);
        return $this;
    }

    /**
     * Setting attribute tab block for kitproduct
     *
     * @param Varien_Object $observer
     * @return MagentoAid_KitProduct_Model_Observer
     */
    public function setAttributeTabBlock($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == MagentoAid_KitProduct_Model_Product_Type::TYPE_KITPRODUCT) {
            Mage::helper('adminhtml/catalog')
                ->setAttributeTabBlock('kitproduct/adminhtml_catalog_product_edit_tab_attributes');
        }
        return $this;
    }

    /**
     * Initialize product options renderer with kitproduct specific params
     *
     * @param Varien_Event_Observer $observer
     * @return MagentoAid_KitProduct_Model_Observer
     */
    public function initOptionRenderer(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $block->addOptionsRenderCfg('kitproduct', 'kitproduct/catalog_product_configuration');
        return $this;
    }

    /**
     * Add price index to kitproduct product after load
     *
     * @deprecated since 1.4.0.0
     *
     * @param Varien_Event_Observer $observer
     * @return MagentoAid_KitProduct_Model_Observer
     */
    public function catalogProductLoadAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == MagentoAid_KitProduct_Model_Product_Type::TYPE_KITPRODUCT) {
            Mage::getSingleton('kitproduct/price_index')
                ->addPriceIndexToProduct($product);
        }

        return $this;
    }

    /**
     * CatalogIndex Indexer after plain reindex process
     *
     * @deprecated since 1.4.0.0
     * @see MagentoAid_KitProduct_Model_Mysql4_Indexer_Price
     *
     * @param Varien_Event_Observer $observer
     * @return MagentoAid_KitProduct_Model_Observer
     */
    public function catalogIndexPlainReindexAfter(Varien_Event_Observer $observer)
    {
        $products = $observer->getEvent()->getProducts();
        Mage::getSingleton('kitproduct/price_index')->reindex($products);

        return $this;
    }
}
