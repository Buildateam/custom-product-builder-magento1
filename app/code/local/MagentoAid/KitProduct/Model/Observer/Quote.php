<?php
class MagentoAid_KitProduct_Model_Observer_Quote
{	
	public function onSalesQuoteItemSetProduct($observer)
	{		    
	    $product = $observer->getEvent()->getProduct();
	    $quoteItem = $observer->getEvent()->getQuoteItem();

		if ( $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE )
		if ( $quoteItem->getParentItem() )
		if ( $quoteItem->getParentItem()->getProductType() == MagentoAid_KitProduct_Model_Product_Type::TYPE_KITPRODUCT )
		{
			$confProductId = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $quoteItem->getProductId() );
			if ( $confProductId )
			{
				$cp = Mage::getModel('catalog/product')->load( $confProductId );
				if ( $cp->getEntityId() )
				{
					$quoteItem->setName( $cp->getName() .  ': ' . $quoteItem->getName() );
				}
			}
		}
	}
}