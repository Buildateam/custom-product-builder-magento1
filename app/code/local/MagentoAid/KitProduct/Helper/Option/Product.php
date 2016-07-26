<?php
class MagentoAid_KitProduct_Helper_Option_Product extends Mage_Core_Helper_Abstract
{
    protected static $_orientationProductIds;

    const ORIENT_LEFT = 'left';
    const ORIENT_RIGHT = 'right';

    public function getOrientLeft()
    {
        return self::ORIENT_LEFT;
    }

    public function getOrientRight()
    {
        return self::ORIENT_RIGHT;
    }

    public function getOrientByProductName($name)
    {
        if ( stristr($name, self::ORIENT_LEFT) )
        {
            return ucfirst( self::ORIENT_LEFT );
        }
        else
        if ( stristr($name, self::ORIENT_RIGHT) )
        {
            return ucfirst( self::ORIENT_RIGHT );
        }

        return null;
    }

    public function getKitOrientationList( $productId )
    {
        if ( !self::$_orientationProductIds )
        {
            $orientLeftProduct = null;
            $orientRightProduct = null;

            $selectionCollection = Mage::getResourceModel('kitproduct/selection_collection')
                ->addFilter('parent_product_id', array('eq' => $productId))
                ->addAttributeToSelect('*')
            ;
            foreach($selectionCollection as $p)
            if ( strtolower($p->getName()) == 'orientation' )
            {
                $orientationSimples = $this->getSortedSimples($p);

                foreach($orientationSimples as $s)
                {
                    //if ( strtolower($s->getName()) == 'left' )
                    if ( stristr($s->getName(), $this->getOrientLeft()) )
                    {
                        $orientLeftProduct = $s;
                    }
                    //if ( strtolower($s->getName()) == 'right' )
                    if ( stristr($s->getName(), $this->getOrientRight()) )
                    {
                        $orientRightProduct = $s;
                    }
                }
            }

            if ( $orientLeftProduct && $orientRightProduct )
            {
                self::$_orientationProductIds = array(
                    $orientLeftProduct->getEntityId() => $this->getOrientByProductName( $orientLeftProduct->getName() ),
                    $orientRightProduct->getEntityId() => $this->getOrientByProductName( $orientRightProduct->getName() )
                );
            }
        }

        if ( self::$_orientationProductIds )
        {
            return self::$_orientationProductIds;
        }

        return null;
    }

    public function getOrientationDropdownOptions($orientList, $productIdSelected = null)
    {
        if ( count($orientList) )
        {
            $selectOptions = '<option value="">Select depends on</option>';
            foreach($orientList as $productId => $name)
            {
                $selected = ($productIdSelected == $productId) ? ' selected ' : '';
                $selectOptions .= "<option value=\"{$productId}\" {$selected} >{$name}</option>";
            }
            return $selectOptions;
        }
        return '';
    }

    /*
    protected static $_optionsProducts;

    public function getByProduct( $product )
	{
	    if ( !self::$_optionsProducts )
        {
		    $collection = Mage::getResourceModel('kitproduct/product_collection');
			$a = $collection->toArray();
			self::$_optionsProducts = $a['items'];
		}		
		
		if ( self::$_optionsProducts )
		if ( is_array(self::$_optionsProducts) )
		if ( count(self::$_optionsProducts) )
		foreach(self::$_optionsProducts as $id => $item)
		{
		    if ( $item['product_id'] == $product->getEntityId() )
			{
			    return $item;
			}
		}
		
		return null;
	}
    */

    public function getSortedSimples( $configurableProduct )
    {
        $simples = Mage::getModel('catalog/product_type_configurable')
            ->getUsedProductCollection( $configurableProduct )
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('description')
            ->addAttributeToSelect('price')
        ;
        $simples
            ->getSelect()
            ->joinInner(
                array('csi' => 'cataloginventory_stock_item'),
                "csi.product_id = e.entity_id",
                array('is_in_stock' => 'csi.is_in_stock')
            )
            ->joinLeft(
                array('kp' => 'catalog_product_kitproduct_product'),
                "kp.product_id = e.entity_id",
                array(
                    'position' => 'kp.position',
                    'depends_on' => 'kp.depends_on'
                )
            )
            ->order("position ASC")
        ;

        return $simples;
    }
}
