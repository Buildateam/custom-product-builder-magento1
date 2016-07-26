<?php
class MagentoAid_KitProduct_Model_Resource_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('kitproduct/product', 'item_id');
    }
}
