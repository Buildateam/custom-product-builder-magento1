<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is kitproductd with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * KitProduct Resource Model
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MagentoAid_KitProduct_Model_Resource_Kitproduct extends Mage_CatalogIndex_Model_Resource_Data_Abstract
{
    /**
     * Preparing select for getting selection's raw data by product id
     * also can be specified extra parameter for limit which columns should be selected
     *
     * @param int $productId
     * @param array $columns
     * @return Zend_DB_Select
     */
    protected function _getSelect($productId, $columns = array())
    {
        return $this->_getReadAdapter()->select()
            ->from(array("kitproduct_option" => $this->getTable('kitproduct/option')), array('type', 'option_id'))
            ->where("kitproduct_option.parent_id = ?", $productId)
            ->where("kitproduct_option.required = 1")
            ->joinLeft(array(
                "kitproduct_selection" => $this->getTable('kitproduct/selection')),
                "kitproduct_selection.option_id = kitproduct_option.option_id",
                $columns
            );
    }

    /**
     * Retrieve selection data for specified product id
     *
     * @param int $productId
     * @return array
     */
    public function getSelectionsData($productId)
    {
        return $this->_getReadAdapter()->fetchAll($this->_getSelect(
            $productId,
            array("*")
        ));
    }

    /**
     * Removing all quote items for specified product
     *
     * @param int $productId
     */
    public function dropAllQuoteChildItems($productId)
    {
        $quoteItemIds = $this->_getReadAdapter()->fetchCol(
            $this->_getReadAdapter()->select()
            ->from($this->getTable('sales/quote_item'), array('item_id'))
            ->where('product_id = :product_id'),
            array('product_id' => $productId)
        );

        if ($quoteItemIds) {
            $this->_getWriteAdapter()->delete(
                $this->getTable('sales/quote_item'),
                array('parent_item_id IN(?)' => $quoteItemIds)
            );
        }
    }

    /**
     * Removes specified selections by ids for specified product id
     *
     * @param int $productId
     * @param array $ids
     */
    public function dropAllUnneededSelections($productId, $ids)
    {
        $where = array(
            'parent_product_id = ?' => $productId
        );
        if (!empty($ids)) {
            $where['selection_id NOT IN (?) '] = $ids;
        }
        $this->_getWriteAdapter()
            ->delete($this->getTable('kitproduct/selection'), $where);
    }

    /**
     * Save product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return MagentoAid_KitProduct_Model_Resource_KitProduct
     */
    public function saveProductRelations($parentId, $childIds)
    {
        Mage::getResourceSingleton('catalog/product_relation')
            ->processRelations($parentId, $childIds);

        return $this;
    }
}
