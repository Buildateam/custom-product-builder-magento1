<?php
class MagentoAid_KitProduct_Model_Product extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('kitproduct/product');
        parent::_construct();
    }
}
