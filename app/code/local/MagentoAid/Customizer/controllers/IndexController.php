<?php
class MagentoAid_Customizer_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

    /*
    public function productAction(){
        //
        //Takes in a product Id and spits out all available data in JSON.
        //

        //$product = Mage::getModel('catalog/product')->getCollection()->getFirstItem();
        if ($postData = $this->getRequest()->getPost()) {

            $product = Mage::getModel('catalog/product')->load($postData['productId']);

            if ($product->getId()) {
                $response = $product->getData();
            } else {
                $response = 'There are no products available with this id';
            }

            $this->getResponse()->setBody(json_encode($response));
        }

    }
    */
}