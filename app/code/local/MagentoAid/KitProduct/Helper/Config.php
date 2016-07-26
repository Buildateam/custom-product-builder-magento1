<?php

class MagentoAid_KitProduct_Helper_Config extends Mage_Core_Helper_Abstract
{
    const HIDE_KITPRODUCTGEN_PRODUCTS = 'kit_product/general/hide_kitproductgen_products';

    public function getHideKitproductgenProducts($store = null)
    {
        return (bool)Mage::getStoreConfig(self::HIDE_KITPRODUCTGEN_PRODUCTS, $store);
    }
}
