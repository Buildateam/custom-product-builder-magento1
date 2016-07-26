<?php
class MagentoAid_KitProduct_Model_Observer_Admin_Action
{
    public function onControllerActionPredispatchAdminhtmlCatalogProductEdit($observer)
	{	
	    $action = $observer->getEvent()->getControllerAction();
        //Mage::log( $action->getFullActionName() ); 
		$productId = (int)Mage::app()->getRequest()->getParam('id');

		if ( $orientList = Mage::helper('kitproduct/option_product')->getKitOrientationList( $productId ) )
		{
			Mage::unregister('orientation_options');
		    Mage::register('orientation_options', $orientList);
		}
	}	
}
