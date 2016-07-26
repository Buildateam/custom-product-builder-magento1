<?php
class MagentoAid_Customizer_AjaxController
extends Mage_Core_Controller_Front_Action
{
    public function productAction()
    {
        $res = array('error' => 0, 'message' => '', 'groups' => '');

        try
        {
            $requestData = $this->getRequest()->getParams();
            $productId = @(int)$requestData['id'];
            if ( empty($productId) )
            {
                throw new Exception('Product ID is not specified.');
            }

            $product = Mage::getModel('catalog/product')->load( $productId ); // Mage_Catalog_Model_Product
            if ( !$product->getEntityId() )
            {
                throw new Exception('Failed to load product with specified ID.');
            }
            if ( $product->getTypeId() != MagentoAid_KitProduct_Model_Product_Type::TYPE_KITPRODUCT )
            {
                throw new Exception('Loaded product is not a Kit product.');
            }

            //$res['data'] = $product->getData();
            $res['groups'] = Mage::helper('kitproduct/json')->prepareArrayForKit( $product );

        }
        catch(Exception $e)
        {
            $res['error']= 1;
            $res['message']= $e->getMessage();
        }

        $json = Zend_Json::encode($res);
        $json = Zend_Json::prettyPrint($json, array('indent' => '  '));
        //Mage::log($res);
        //Mage::log($json);

        $this->getResponse()->setBody($json);
    }
}