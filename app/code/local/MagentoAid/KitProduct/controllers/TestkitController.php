<?php
//require_once 'Mage' . DS . 'Checkout' . DS . 'controllers' . DS . 'CartController.php';

class MagentoAid_KitProduct_TestkitController
//extends Mage_Checkout_CartController
extends Mage_Core_Controller_Front_Action
{
    public function addkitAction()
    {
        try
        {
            $product = Mage::getModel('catalog/product')->load( 347740 );
            //var_dump( $product->getName() );

            $cart = Mage::getSingleton('checkout/cart');

            $params = array(
                'product' => 347740,
                'qty' => 1,

                'buy_requests' => array(
                    347741 => new Varien_Object(array(          # Orientation
                        'product' => 347743,                    // Right
                        //'super_attribute' => array(162 => 18),// 17 Left, 18 Right
                        'qty' => 1,
                    )),
                    347744 => new Varien_Object(array(          # Design
                        'product' => 347745,                    // Honey
                        // 'super_attribute' => array(162 => 231)  // Honey
                        'qty' => 1,
                    )),


                    347757 => new Varien_Object(array(          # Neck Color
                        'product' => 347762,                    // Red Neck
                        //'super_attribute' => array(162 => 337)  // Red Neck
                        'qty' => 1,
                    )),
                    347765 => new Varien_Object(array(          # Headstock
                        'product' => 347768,                    // Red Headstock
                        //'super_attribute' => array(162 => 345)  // Red Headstock
                        'qty' => 1,
                    )),
                    347766 => new Varien_Object(array(          # Tuners
                        'product' => 347774,                    // Black Tuner
                        //'super_attribute' => array(162 => 350)  // Black Tuner
                        'qty' => 1,
                    )),


                    347776 => new Varien_Object(array(          # Pickups
                        'product' => 347788,
                        //'super_attribute' => array(162 => 245)  // Dual White Humbuckers
                        'qty' => 1,
                    )),
                    347777 => new Varien_Object(array(          # Bridge
                        'product' => 348049,
                        //'super_attribute' => array(162 => 482) // Gold Bridge ??  481 left, 482 Right
                        'qty' => 1,
                    )),
                    347778 => new Varien_Object(array(          # Tailpiece
                        'product' => 347795,
                        //'super_attribute' => array(162 => 357)  // Gold Tailpiece
                        'qty' => 1,
                    )),
                    347889 => new Varien_Object(array(          # Knobs
                        'product' => 348046,
                        //'super_attribute' => array(162 => 490)  // Wood Knob ??  489 Left, 490 Right
                        'qty' => 1,
                    )),
                ),


                'kit_product_option' => array(
                 // optionId => selections
                    1 => array(
                        1 => 1, //347742,   // Orientation
                        2 => 2, //347745    // Design
                    ),

                    2 => array(
                        3 => 3, //347763,   // Neck Color
                        4 => 4, //347769,   // Headstock
                        5 => 5, //347774    // Tuners
                    ),

                    3 => array(
                        6 =>  6, //347782,  // Pickups
                        7 =>  7, //347791,  // Bridge
                        8 =>  8, //347795,  // Tailpiece
                       33 => 33, //348043   // Knobs
                    )

                )

            );
            $cart->addProduct($product, $params);

            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        }
        catch(Exception $e)
        {
            echo '<pre>';
            echo $e->getMessage();
            echo '</pre>';
        }


    }

    public function kitAction()
    {
        Mage::log('kit custom options');
        $kit = Mage::getModel('catalog/product')->load( 347740 );
        foreach($kit->getCustomOptions() as $coo)
        {
            Mage::log( unserialize($coo->getValue()) );  // Mage_Catalog_Model_Product_Configuration_Item_Option

        }
    }
}