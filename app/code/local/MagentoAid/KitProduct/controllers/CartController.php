<?php

class MagentoAid_KitProduct_CartController extends Mage_Core_Controller_Front_Action
{
    protected $_quoteItems;

    public function loadStock(&$product)
    {
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($product->getId());
        }
        $product->setStockItem($stockItem);
    }

    protected function _getQuoteItems()
    {
        if( ! $this->_quoteItems) {
            $this->_quoteItems = Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection();
        }
        return $this->_quoteItems;
    }

    protected function _isProductInQuote($product)
    {
        foreach ($this->_getQuoteItems() as $item) {
            if($item->getProductId() == $product->getId() ||
            $item->getParentId() == $product->getId()) {
                return true;
            }
        }
        return false;
    }

    public function addAction()
    {
        $params = $this->getRequest()->getParams();
        /** @var $cart Mage_Checkout_Model_Cart*/
        $cart = Mage::getSingleton('checkout/cart');
        //$kitProduct = Mage::getModel('catalog/product')->load($params['kit_product_id']);
        $session = $cart->getCheckoutSession();
        $session->setData('kitproduct_id',@$params['kit_product_id']);
        $configurableIds = array_keys($params['kit_option_value']);
        $configurables = Mage::getResourceModel('catalog/product_collection')->addFieldToFilter('entity_id',array('in'=>$configurableIds));

        foreach($configurables as $k => $configurable) {
            if($this->_isProductInQuote($configurable)) {
                continue;
            }
            $optionId = array_keys($params['kit_option_value'][$configurable->getId()]);

            $paramz = array(
                'product' => $configurable->getId(),
                'super_attribute' => array(
                    $optionId[0] =>$params['kit_option_value'][$configurable->getId()][$optionId[0]] ,
                ),
                'qty' => 1,
            );
            $this->loadStock($configurable);
            try {
                $cart->addProduct($configurable, $paramz);

            } catch(Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }
        }
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        return $this->_redirect('checkout/cart');
    }
}