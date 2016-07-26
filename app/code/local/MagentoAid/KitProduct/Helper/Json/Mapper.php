<?php
class MagentoAid_KitProduct_Helper_Json_Mapper extends Mage_Core_Helper_Abstract
{
    protected $_kitProduct;

    protected $_map = array(
        'group' => array(
            'option_id'     => 'id',
            'default_title' => 'name',
            'position'      => 'order',
            // => 'subgroups'
        ),
        'subgroup' => array(
            'selection_id' => 'id',
            'name'         => 'name',
            'position'     => 'order',
            // => 'options'
        ),
        'option' => array(
            'entity_id'    => 'id',
            'name'         => 'name',
            'position'     => 'order',
            // => 'zoomUrl'
            // => 'imageUrl'
            // => 'iconUrl'
            'price'        => 'price',
            'description'  => 'description',
            'depends_on'   => 'depends_on',
        ),
    );

    public function setKitProduct($kitProduct)
    {
        $this->_kitProduct = $kitProduct;
    }

    public function getKitProduct()
    {
        return $this->_kitProduct;
    }

    public function prepare($obj, $type)
    {
        if ( empty($this->_map[ $type ]) )
        {
            throw new Exception('Failed to load map by this type.');
        }

        $data = array();
        foreach($this->_map[ $type ] as $dbKey => $jsonKey)
        {
            $val = $obj->getData($dbKey);
            if ( !empty($val) )
            {
                $data[$jsonKey]= $val;
                $this->_prepareFieldAfter($obj, $type, $dbKey, $jsonKey, $data, $val);
            }
        }

        $this->_prepareAfter($obj, $type, $data);

        return $data;
    }

    protected function _prepareFieldAfter($obj, $type, $dbKey, $jsonKey, &$data, $val)
    {
        if ( in_array($jsonKey, array('id', 'order')) )
        {
            if ( isset($data[$jsonKey]) )
            {
                $data[$jsonKey]= (int)$data[$jsonKey];
            }
        }
        if ( in_array($jsonKey, array('price')) )
        {

            if ( isset($data[$jsonKey]) )
            {
                //Mage::log( $data[$jsonKey] );
                $data[$jsonKey]= floatval( $data[$jsonKey] );
            }
        }
    }

    protected function _prepareAfter($obj, $type, &$data)
    {
        $mediaCatalogProduct = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
        $imgDir = $mediaCatalogProduct . DS . Mage::helper('kitproduct/catalog_product_image')->getImagePath( $this->getKitProduct() );

        if ( $type == 'option' )
        {
            $data['order']= (int)$obj->getData('position');

            $imgZoom = $obj->getSku()  . '_image.png';
            $imgImage = $obj->getSku() . '_smallimage.png';
            $imgIcon = $obj->getSku()  . '_thumbnail.png';

            if ( file_exists($imgDir . $imgZoom) )
            {
                $data['zoomUrl'] = $imgZoom;
            }
            $data['imageUrl']= $imgImage;
            $data['iconUrl']= $imgIcon;

            $data['is_salable']= $obj->getIsSalable();
        }
    }
}
