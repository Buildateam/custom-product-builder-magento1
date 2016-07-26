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
 * Helper for fetching properties by product configurational item
 *
 * @category   Mage
 * @package    MagentoAid_KitProduct
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class MagentoAid_KitProduct_Helper_Catalog_Product_Configuration extends Mage_Core_Helper_Abstract
    implements Mage_Catalog_Helper_Product_Configuration_Interface
{
    protected $_configChildsMap = array();
    /**
     * Get selection quantity
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $selectionId
     * @return decimal
     */
    public function getSelectionQty($product, $selectionId)
    {
        $selectionQty = $product->getCustomOption('selection_qty_' . $selectionId);
        if ($selectionQty) {
            return $selectionQty->getValue();
        }
        return 0;
    }

    /**
     * Obtain final price of selection in a kitproduct product
     *
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @param Mage_Catalog_Model_Product $selectionProduct
     * @return decimal
     */
    public function getSelectionFinalPrice(Mage_Catalog_Model_Product_Configuration_Item_Interface $item,
        $selectionProduct)
    {
        $selectionProduct->unsetData('final_price');
        return $item->getProduct()->getPriceModel()->getSelectionFinalTotalPrice(
            $item->getProduct(),
            $selectionProduct,
            $item->getQty() * 1,
            $this->getSelectionQty($item->getProduct(), $selectionProduct->getSelectionId()),
            false,
            true
        );
    }

    /**
     * Get kitproductd selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @return array
     */
    public function getKitProductOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $options = array();
        $product = $item->getProduct();

        $simpleProductIds = array();
        if ( $item->getOptionByCode('kitproduct_simple_product_ids') )
        {
            $simpleProductIds = unserialize( $item->getOptionByCode('kitproduct_simple_product_ids')->getValue() );
        }


        /**
         * @var MagentoAid_KitProduct_Model_Product_Type
         */
        $typeInstance = $product->getTypeInstance(true);

        // get kitproduct options
        $optionsQuoteItemOption = $item->getOptionByCode('kitproduct_option_ids');
        $kitproductOptionsIds = $optionsQuoteItemOption ? unserialize($optionsQuoteItemOption->getValue()) : array();
        if ($kitproductOptionsIds) {
            /**
            * @var MagentoAid_KitProduct_Model_Mysql4_Option_Collection
            */
            $optionsCollection = $typeInstance->getOptionsByIds($kitproductOptionsIds, $product);

            // get and add kitproduct selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('kitproduct_selection_ids');

            $selectionsCollection = $typeInstance->getSelectionsByIds(
                unserialize($selectionsQuoteItemOption->getValue()),
                $product
            );



            $kitproductOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($kitproductOptions as $kitproductOption) {
                if ($kitproductOption->getSelections()) {
                    $option = array(
                        'label' => $kitproductOption->getTitle(),
                        'value' => array()
                    );

                    $kitproductSelections = $kitproductOption->getSelections();


                    foreach ($kitproductSelections as $kitproductSelection) {


                        // Get choosed configurable's simple
                        $simple = Mage::getModel('catalog/product_type_configurable')
                            ->getUsedProductCollection( $kitproductSelection )
                            ->addAttributeToSelect( array('name', 'price') )
                            ->addFieldToFilter('entity_id', array('in' => $simpleProductIds))
                            ->getFirstItem()
                        ;


                        $qty = $this->getSelectionQty($product, $kitproductSelection->getSelectionId()) * 1;
                        if ($qty) {
                            /*
                            $option['value'][] = $qty . ' x ' . $this->escapeHtml($kitproductSelection->getName())
                                . ' ' . Mage::helper('core')->currency(
                                    $this->getSelectionFinalPrice($item, $kitproductSelection)
                                );
                            */
                            $priceHtml = Mage::helper('core')->currency( $simple->getPrice() );
                            $option['value'][]= $kitproductSelection->getName() . ': ' .  $simple->getName() . ' ' . $priceHtml;
                        }
                    }

                    if ($option['value']) {
                        $options[] = $option;
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Retrieves product options list
     *
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @return array
     */
    public function getOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        return array_merge(
            $this->getKitProductOptions($item),
            Mage::helper('catalog/product_configuration')->getCustomOptions($item)
        );
    }

    public function getConfigChilds($product)
    {
        if(! array_key_exists($product->getId(),$this->_configChildsMap)) {
            $childs = $product->getTypeInstance(true)->getUsedProducts(null,$product);
            foreach ($childs as $k => $child) {
                $childs[$k] = $child->setStore(Mage::app()->getStore()->getId())->load($child->getId());

            }
            $this->_configChildsMap[$product->getId()] = $childs;
        }
        return (array)$this->_configChildsMap[$product->getId()];
    }

    public function getOptionConfig($product,$option = array())
    {
        $mappedArray = array();
        $option = new Varien_Object($option);
        $childProducts = $this->getConfigChilds($product);
        $_size_attribute    = Mage::getModel('eav/config')->getAttribute('catalog_product','size');
        $_size_array        = $_size_attribute->getSource()->getAllOptions();

        $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

        $_size_sorter       = array();
        $_products          = array();
        $_products_sorted   = array();

        $_children          = $product->getTypeInstance(true)
            ->getUsedProducts(null, $product);

        foreach ($_children as $_child) {
            $_child_size    = $_child->getAttributeText('size');
            $_products[$_child_size] = $_child;
        }
        foreach ($_size_array as $_size) {
            array_push($_size_sorter, $_size['label']);
        }
        foreach($_size_sorter as $_size) {
            if(array_key_exists($_size,$_products)) {
                array_push($_products_sorted,$_products[$_size]);
                unset($_products[$_size]);
            }
        }
        $_products          = $_products_sorted;
        $_auto_select       = false;
        if(count($_products) == 1) {
            $_auto_select = true;
    }
        //var_dump($_products);
        foreach ($_products as $child) {

            foreach ($productAttributeOptions as $productAttribute) {

                foreach ($productAttribute['values'] as $k => $attribute) {
                    /*var_dump($child->getData($productAttribute['attribute_code']));*/
                    if($child->getData($productAttribute['attribute_code']) == $attribute['value_index']) {
                        $i = 0;
                        $imgs = array();
                        foreach ($product->getMediaGalleryImages() as $image) {
                            if($i == 3) {break;}
                            $imgs[] = (string)Mage::helper('catalog/image')->init($product, 'image',$image->getFile())->resize(50, 89);
                            $i++;
                        }
                        $mappedArray[] = array(
                            'configurable_id'       => $child->getParentId(),
                            'product_id'            => $child->getId(),
                            'position'              => $productAttribute['position'],
                            'option_id'             => $productAttribute['id'],
                            'value'                 => $attribute['value_index'],
                            'label'                 => $attribute['store_label'],
                            'super_attribute'       => $productAttribute['attribute_id'],
                            'parent_id'             => $product->getId(),
                            'price'                 => $product->getFinalPrice(),
                            'in_stock'              => $child->isSaleable(),
                            'name'                  => $product->getName(),
                            'images'                => $imgs,
                            'is_required'           => $option->getRequired()
                        );
                    }
                    $attributeOptions[$productAttribute['label']][$attribute['value_index']] = $attribute['store_label'];

                }

            }
        }
        return $mappedArray;


    }
}
