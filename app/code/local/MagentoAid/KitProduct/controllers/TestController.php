<?php
class MagentoAid_KitProduct_TestController 
extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $cp = Mage::getModel('catalog/product')->load( 347622 );
		$cp->setName('sg122');
		$cp->save();
    }

    public function transactionAction()
    {
        /*
        $o = Mage::getModel('catalog/product')->load( 347819 );
        var_dump( $o->getName() );
        //$o->isDeleted( true );
        $o->save();
        */

        $adapter = Mage::getSingleton("core/resource")->getConnection("core_write");
        
        try
        {
		    $adapter->beginTransaction();
		
            $o = Mage::getModel('kitproduct/product');
            $o->setProductId(347986);
			$o->setPosition(77);
            $o->save();

            //throw new Exception('EEEEE');

            $o = Mage::getModel('kitproduct/product');
            $o->setProductId(347985);
			$o->setPosition(88);
            $o->save();

            $adapter->commit();
        }
        catch(Exception $e)
        {
            $adapter->rollback();
            throw $e;
        }
    }

    public function productAction()
    {
        $p = Mage::getModel('catalog/product')->load( 347893 );
        var_dump( $p->getIsSalable() ); // 0 - OutOfStock, Disabled


    }

    public function opAction()
    {
        $collection = Mage::getResourceModel('kitproduct/product_collection');
        echo '<pre>';
        var_dump( $collection->toArray() );
        echo '</pre>';
    }

    public function jsonAction()
    {
        /*
        $p = Mage::getModel('catalog/product')->load( 347982 );
        var_dump( $p->getData('kit_json_data') );

        echo '<br/>';
        //echo $p->toJson(array('kit_json_data'));
        echo '<br/><br/>';

        $collection = Mage::getResourceModel('catalog/product_collection')
          ->addFieldToFilter('type_id', array('eq' => MagentoAid_KitProduct_Model_Product_Type::TYPE_KITPRODUCT))
        ;
        var_dump( count($collection) );
        foreach($collection as $p)
        {
            //echo '<br/>';

        }
        */


        $id = 347740; // 347982  347740
        $product = Mage::getModel('catalog/product')->load( $id );

        $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
        /*
        $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
            $product->getTypeInstance(true)->getOptionsIds($product),
            $product
        );
        $optionCollection->appendSelections($selectionCollection);
        */
        echo '<pre>';
        $i = 0;
        foreach ($optionCollection as $option)
        {
            echo '<br/><br/><br/>group<br/><br/>';
            var_dump( $option->getData() );


            //foreach ($option->getSelections() as $selection)
            foreach($option->getSelectionsCollection() as $selection)
            {
                echo '<br/><br/><br/>subgroup<br/><br/>';
                var_dump( $selection->getData() );

                $simples = Mage::helper('kitproduct/option_product')->getSortedSimples( $selection );

                foreach($simples as $s)
                {
                    echo '<br/><br/><br/>option<br/><br/>';
                    var_dump( $s->getName() );
                    var_dump( $s->getSku() );
                    var_dump( $s->getPrice() );
                    var_dump( $s->getDescription() );

                }

            }
            $i++;
        }
        echo '</pre>';

    }

    public function json2Action()
    {
        // 347743  simple
        $id = 347740; // 347982  347740
        $product = Mage::getModel('catalog/product')->load( $id );

        $json = Mage::helper('kitproduct/json')->prepareForKit( $product );
        echo '<pre>';
        echo $json;
        echo '</pre>';
    }

    public function intAction()
    {
        //$a = array("k1"=> 111, "k2"=> 222, "price" => "75.0000", 4445 => 11111);
        $a = array(11 => 111, 22 => 222);
        //$a['price']= (float)$a['price'];
        //echo Zend_Json::encode($a);
        echo json_encode($a);

        //echo (float)"75.28";
    }

    public function menuAction()
    {
        var_dump( Mage::helper('kitproduct/config')->getHideKitproductgenProducts() );
    }

    public function cpAction()
    {
        $cp = Mage::getModel('catalog/product')->load( 347757 );
        var_dump( $cp->getIsSalable() );
    }
}