<?php
/**
 * Class MagentoAid_KitProduct_Ajax_JsonController
 */
class MagentoAid_KitProduct_Ajax_JsonController extends Mage_Core_Controller_Front_Action {


    /**
     * Controller that generates JSON for reacts
     */
    public function indexAction(){
        $id  = $this->getRequest()->getParam('id');

        $json = array();

        // initializing categories

        $json['groups'] = array();

        if(!empty($id)){
            $product = Mage::getModel('catalog/product')->load($id);

            $kitproduct = $product->getTypeInstance();

            $options = $kitproduct->getOptionsCollection();

            $selections = $kitproduct->getSelectionsCollection($options->getAllIds(), $product);

            foreach($selections as $index => $selection){
                $sku = explode('_',$selection->getSku());

                if(isset($sku[1]) && isset($sku[2])) {
                    $group = ucfirst($sku[1]);
                    $subgroup = ucfirst($sku[2]);
                }

                $optionsProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$selection);

                foreach($optionsProducts as $option) {
                    $sku = explode('_',$option->getSku());
                    if(isset($sku[3])){
                        $optionTitle = ucfirst($sku[3]);
                    }
                    $optionsArr[] = array(
                        'id' => $optionTitle,
                        'name' => $optionTitle,
                        'imageUrl' => '',
                        'iconUrl' => ''
                    );
                }

                $json['groups'][] = array(
                    'id' => $selection->getSelectionId(),
                    'name'=> $group,
                    'subgroups' => array( array(
                        "id" => $selection->getOptionId(),
                        "name" => $subgroup,
                        "order" => 1,
                        "options" => $optionsArr
                    ) )
                );


            }
        }else{
            // handle empty id
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($json));
    }


}