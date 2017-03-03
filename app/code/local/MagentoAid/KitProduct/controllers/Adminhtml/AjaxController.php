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
 * Adminhtml selection grid controller
 *
 * @category    Mage
 * @package     MagentoAid_KitProduct
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MagentoAid_KitProduct_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        $this->setUsedModuleName('MagentoAid_KitProduct');
    }

    public function _isAllowed()
    {
        return true;
    }

    /**
     * Return module's helper
     * @return MagentoAid_KitProduct_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('kitproduct');
    }

    public function saveOptionsAction()
    {
        $res = array(
            'error' => 0,
            'message' => '',
            'data' => array()
        );
        $data = $this->getRequest()->getParams();

        $adapter = Mage::getSingleton("core/resource")->getConnection("core_write");
        try
        {
            $adapter->beginTransaction();

            $this->_saveOptions($res, $data);

            $adapter->commit();
        }
        catch(Exception $e)
        {
            $adapter->rollback();

            $res['error']= 1;
            $res['message']= $e->getMessage();
            Mage::logException( $e );
        }

        $this->getResponse()->setBody(Zend_Json::encode($res));
    }

    protected function _saveOptions(&$res, $data)
    {
        $groups = $data['kitproduct_options'];
        $subgroups = $data['kitproduct_selections'];
        $subgroupName = '';

        foreach ($groups as $k => $group) {
            $parentId = 0;
            $childrenIds = array();

            foreach ($subgroups[$k] as $x => $subgroup) {

                if ( $this->_processDeleteSubgroupProduct( $subgroup ) )
                {
                    continue;
                }


                $childrenIds = array();


                $products = empty($subgroup['products']) ? null : Zend_Json::decode($subgroup['products']);
                $confProductId = null;
                if ( $products )
                    if ( count($products) && isset($products['parent_id']) && isset($products['child_ids']) )
                    {
                        $confProductId = $products['parent_id'];
                    }

                if (isset($subgroup['name'])) { // is a sub group
                    $productGenerated = $this->_getHelper()->createConfigurableProduct($confProductId, $data['product']['name'], $group['title'], $subgroup['name']);
                    $parentId = $productGenerated->getId();
                    if (count($subgroup) > 2) {
                        foreach ($subgroup as $v => $option) {
                            Mage::unregister('kit_option');
                            Mage::register('kit_option', $option);

                            $simpleProductId = empty( $option['product_id'] ) ? null : (int)$option['product_id'];

                            if (is_int($v)) {

                                # hardcode: add ' Left' / ' Right' to option title
                                if ( !empty($option['depends_on']) )
                                if ( $orientOptions = Mage::registry('orientation_options') )
                                {
                                    if ( isset($orientOptions[ $option['depends_on'] ]) )
                                    {
                                        $option['title'] .= ' ' . $orientOptions[ $option['depends_on'] ];
                                    }
                                }

                                $optionId = $this->_getHelper()->getOptionId($option['title']);
                                if ($optionId == 0) {
                                    $this->_getHelper()->addOption($option['title']);
                                    $optionId = $this->_getHelper()->getOptionId($option['title']);
                                }

                                # hardcode: remove ' Left' / ' Right' from option title
                                if ( !empty($option['depends_on']) )
                                if ( $orientOptions = Mage::registry('orientation_options') )
                                {
                                    if ( isset($orientOptions[ $option['depends_on'] ]) )
                                    {
                                        $option['title'] = str_replace(' ' . $orientOptions[ $option['depends_on'] ], '', $option['title']);
                                    }
                                }

                                $price = $option['price'];

                                $productGenerated = $this->_getHelper()->createSimpleProduct($simpleProductId, $data['product']['name'], $group['title'], $subgroup['name'], $option['title'], '', '', $optionId, $price);
                                if ( empty($option['delete']) )
                                {
                                    $childrenIds[] = $productGenerated->getId();
                                }
                            }

                            $this->_processDeleteOptionProduct( $option );
                        }
                    }
                    $subgroupName = $subgroup['name'];
                } else {
                    foreach ($subgroup as $v => $option) {
                        Mage::unregister('kit_option');
                        Mage::register('kit_option', $option);

                        $simpleProductId = empty( $option['product_id'] ) ? null : (int)$option['product_id'];

                        $optionId = $this->_getHelper()->getOptionId($option['title']);
                        if ($optionId == 0) {
                            $this->_getHelper()->addOption($option['title']);
                            $optionId = $this->_getHelper()->getOptionId($option['title']);
                        }
                        $price = $option['price'];
                        $productGenerated = $this->_getHelper()->createSimpleProduct($simpleProductId, $data['product']['name'], $group['title'], $subgroup['name'], $option['title'], '', '', $optionId, $price);
                        if ( empty($option['delete']) )
                        {
                            $childrenIds[] = $productGenerated->getId();
                        }


                    }

                }

                $this->_getHelper()->addOptionsToSubgroup($parentId, $childrenIds);

                $keyPrefix = Mage::helper('kitproduct/json')->getJsonKeyPrefix();
                if ( !isset($res['data'][$keyPrefix . $k]) )
                {
                    $res['data'][$keyPrefix . $k]= array();
                }

                $res['data'][$keyPrefix . $k][$keyPrefix . $x]= array(
                    'parent_id' => $parentId,
                    'child_ids' => $childrenIds,
                );
            }

        }
    }

    protected function _processDeleteSubgroupProduct( $subgroup )
    {
        if ( $subgroup )
        if ( is_array($subgroup) )
        if ( (!empty($subgroup['delete'])) && (!empty($subgroup['products'])) )
        {
            $products = Zend_Json::decode($subgroup['products']);
            if ( $confProductId = (int)$products['parent_id'])
            {
                $c = Mage::getModel('catalog/product')->load( $confProductId );
                if ( $c->getEntityId() )
                {
                    $simples = Mage::getModel('catalog/product_type_configurable')
                        ->getUsedProductCollection( $c )
                    ;
                    foreach($simples as $s)
                    {
                        $s->delete();
                    }

                    $c->delete();
                }

                return true;
            }
        }

        return false;
    }

    protected function _processDeleteOptionProduct( $option )
    {
        if ( $option )
        if ( is_array($option) )
        if ( (!empty($option['delete'])) && (!empty($option['product_id'])) )
        {
            $simpleProductId = (int)$option['product_id'];
            $s = Mage::getModel('catalog/product')->load( $simpleProductId );
            if ( $s->getEntityId() )
            {
                $s->delete();
            }
        }
    }
}
