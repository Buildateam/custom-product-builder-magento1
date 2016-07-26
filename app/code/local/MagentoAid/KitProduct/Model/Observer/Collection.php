<?php
class MagentoAid_KitProduct_Model_Observer_Collection
{	
	public function onEavCollectionAbstractLoadBefore($observer)
	{		    
	    $collection = $observer->getEvent()->getCollection();

		if ( Mage::app()->getStore()->isAdmin() )
		{
            $this->_adminProductGridHideKitGeneratedProducts($collection);
		}	
	}

    protected function _adminProductGridHideKitGeneratedProducts($collection)
    {
        if ( Mage::helper('kitproduct/config')->getHideKitproductgenProducts() )
        if ( $collection instanceof Mage_Catalog_Model_Resource_Product_Collection )
        if (
            stristr(Mage::app()->getRequest()->getRequestUri(), '/catalog_product/index')
            || stristr(Mage::app()->getRequest()->getRequestUri(), '/catalog_product/grid')
        )
        {
            // kitproductgen
            $as = Mage::getModel('eav/entity_attribute_set')->load(MagentoAid_KitProduct_Helper_Data::ATTRIBUTE_SET_KIT, 'attribute_set_name');
            if ( $as->getAttributeSetId() )
            {
                $collection->addFieldToFilter('attribute_set_id', array('neq' => $as->getAttributeSetId()));
            }
        }
    }
}
