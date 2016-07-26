<?php
require_once('app/code/core/Mage/Catalog/controllers/ProductController.php');
class MagentoAid_KitProduct_AjaxController extends Mage_Catalog_ProductController
{
    public function getProductDataAction()
    {
        $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load($this->getRequest()->getParam('product_id'));
        $i = 0;
        $imgs = array();
        foreach ($product->getMediaGalleryImages() as $image) {
            if($i == 3) {break;}
            $imgs[] = $image->getUrl();
            $i++;
        }
        $response = array(
            'images' => $imgs,
            'name' => $product->getName(),
            'product_id' => $product->getId()
        );
        $this->getResponse()->setBody(json_encode($response));
    }

    public function getProductModalAction()
    {
        $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load($this->getRequest()->getParam('product_id'));
        $this->loadLayout();
        $html = $this->getLayout()->createBlock('kitproduct/catalog_product_quickmodal')
            ->setTemplate('kitproduct/catalog/product/quickmodal.phtml')
            ->setProduct($product)
            ->toHtml();
        return $this->getResponse()->setBody(json_encode(array('modal_html'=>$html)));
    }



    protected function _prepareAddData(&$data)
    {
        foreach($data['buy_requests'] as $configurableProductId => $request)
        {
            $data['buy_requests'][$configurableProductId]= new Varien_Object($request);
        }

        $data['kit_product_option']= array();

        $selections = Mage::getResourceModel('kitproduct/selection_collection');
        $selections->getSelect()
          ->where('selection.parent_product_id = ?', (int)$data['product'])
        ;
        foreach($selections as $selection)
        {
            if ( ( $selection->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED ) || (!$selection->getIsInStock()) )
            {
                continue;
            }

            if ( !isset($data['kit_product_option'][$selection->getOptionId()]) )
            {
                $data['kit_product_option'][$selection->getOptionId()]= array();
            }

            $data['kit_product_option'][$selection->getOptionId()][$selection->getSelectionId()]= $selection->getSelectionId();
        }

    }

    protected function _afterAdd($product, &$res)
    {
        $store = Mage::app()->getStore();
        $code  = $store->getCode();
        $aspect_ratio = Mage::getStoreConfig("mango_settings/category/aspect_ratio",$code);
        $ratio_width = Mage::getStoreConfig("mango_settings/category/ratio_width",$code);
        $ratio_height = Mage::getStoreConfig("mango_settings/category/ratio_height",$code);
        if ( $aspect_ratio )
        {
            $product_image_src = Mage::helper('catalog/image')->init($product, 'small_image')->constrainOnly(FALSE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize(250);
        }
        else
        {
            $product_image_src=Mage::helper('catalog/image')->init($product, 'small_image')->resize($ratio_width,$ratio_height);

        }

        $product_image = '<img src="' . $product_image_src . '" class="product-image" alt=""/>';
        $message = '<div class="msg">' . $this->__("You've just added this product to the cart:") . '<p class="product-name theme-color">'.Mage::helper('core')->htmlEscape($product->getName()).'</p><div class="timer theme-color">5</div></div>'.$product_image;


        $this->loadLayout();
        $toplink = "";
        if($this->getLayout()->getBlock('minicart'))
        {
            $toplink = $this->getLayout()->getBlock('minicart')->toHtml();
        }
        $cart_sidebar = "";
        if($this->getLayout()->getBlock('cart_sidebar'))
        {
            $cart_sidebar = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
        }

        $res['popup_added'] = $message;
        $res['toplink'] = $toplink;
        $res['cart_sidebar'] = $cart_sidebar;
    }

    public function addAction()
    {
        $res = array('error' => 0, 'message' => '');

        try
        {
            $data = $this->getRequest()->getParams();
            if ( !empty($data['buy_requests']) )
            {
                $this->_prepareAddData($data);
            }

            //Mage::log( $data );


            $product = Mage::getModel('catalog/product')->load( (int)$data['product'] );
            $cart = Mage::getSingleton('checkout/cart');
            $cart->addProduct($product, $data);
            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);


            $popup = $this->_afterAdd($product, $res);
        }
        catch(Exception $e)
        {
            $res['error']= 1;
            $res['message']= $e->getMessage();
        }

        $this->getResponse()->appendBody(Zend_Json::encode($res));
    }
}